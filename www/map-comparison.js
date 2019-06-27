(function() {
	if( typeof window.tfmpm == 'undefined' ) window.tfmpm = {};
	
	const bumpAudio = new Audio('./bump.mp3');
	bumpAudio.preload = 'auto';
	
	function ucfirst(str) {
		return str.charAt(0).toUpperCase() + str.slice(1);
	}

	function count(obj) {
		let c = 0;
		for( let k in obj ) ++c;
		return c;
	}
	
	function clearChildren(elem) {
		while(elem.firstChild) {
			elem.removeChild(elem.firstChild);
		}
	}
	
	var MapComparisonUI = function(params) {
		this.schema = params.schema;
		this.maps = params.maps;
		this.infoOverlayElement = params.infoOverlayElement;
		this.mapImageElement = params.mapImageElement;
		this.mapInfoTbody = params.mapInfoTbody;
		this.mapResourceTbody = params.mapResourceTbody;
		this.mapNavigationTbody = params.mapNavigationTbody;
		this.cursorPositionElement = params.cursorPositionElement;
		this.backgroundElement = params.backgroundElement;
		this.currentMapKey = undefined;
		this.uriResolverPrefix = "uri-res/raw/";
		this.mapAttributeMetadata = [];
		this.cursorPixelPosition = undefined;
		let keyedMapAttributeMetadata = {};
		let keyedResourceNames = {};
		for( let m in this.maps ) {
			let map = this.maps[m];
			if( map.resourceStats && count(map.resourceStats) ) {
				for( let k in map.resourceStats ) {
					keyedResourceNames[k] = k;
				}
			}
			for( let k in map ) {
				if( keyedMapAttributeMetadata[k] == undefined ) {
					let attrInfo = {
						'code': k
					};
					this.mapAttributeMetadata.push(attrInfo);
					keyedMapAttributeMetadata[k] = attrInfo;
				}
			}
		}
		this.mapGraph = undefined;
		this.resourceNames = [];
		for( let k in keyedResourceNames ) this.resourceNames.push(k);
		this.resourceNames.sort();
	}
	MapComparisonUI.prototype.getCurrentMap = function() {
		if( this.currentMapKey == undefined ) return undefined;
		return this.maps[this.currentMapKey];
	}
	MapComparisonUI.prototype.mapImageUrl = function(map) {
		return this.uriResolverPrefix + map.mapImageUrn + "/" + map.generationId + ".png";
	};
	MapComparisonUI.prototype.createTd = function(value, className, widthPercentage) {
		if( value == undefined ) value = " ";
		let td = document.createElement('td');
		let valueNode;
		if( typeof(value) == 'string' ) {
			valueNode = document.createTextNode(value);
		} else if( value instanceof Node) {
			valueNode = value;
		} else {
			valueNode = document.createTextNode(""+value);
			//throw new Error("Don't know how to turn "+value+" into a DOM node");
		}
		td.appendChild(valueNode);
		if( className ) td.className = className;
		if( widthPercentage != undefined ) td.setAttribute('width', widthPercentage+'%');
		return td;
	}
	MapComparisonUI.prototype.createKvTr = function(k, v, kClassName) {
		let tr = document.createElement('tr');
		let kTd = document.createElement('td');
		tr.appendChild(this.createTd(k, kClassName));
		tr.appendChild(this.createTd(this.formatValue(v, k), 'code-value'));
		return tr;
	}
	MapComparisonUI.prototype.formatCommitId = function(commitId) {
		let textNode = document.createTextNode(commitId);
		let textElem = document.createElement('a');
		textElem.setAttribute('href', "https://github.com/wube/Factorio/commit/"+commitId);
		textElem.appendChild(textNode);
		textElem.className = 'shortenable-commit-id';
		return textElem;
	}
	MapComparisonUI.prototype.formatValue = function(value, dim) {
		if( /^urn:/.exec(value) ) {
			let link = document.createElement('a');
			link.setAttribute('href', "uri-res/raw/"+value);
			link.appendChild(document.createTextNode(value));
			return link;
		}
		let m;
		if( dim == 'commitId' && (m = /^([a-f0-9]{40})-([a-f0-9]{40})$/.exec(value)) ) {
			let span = document.createElement('span');
			span.appendChild(this.formatCommitId(m[1]));
			span.appendChild(document.createTextNode('-'));
			span.appendChild(this.formatCommitId(m[2]));
			return span;
		}
		return value;
	}
	MapComparisonUI.prototype.createNavTr = function(dim, prevVal, prevSym, curVal, nextSym, nextVal) {
		let tr = document.createElement('tr');
		tr.appendChild(this.createTd(this.formatValue(prevVal, dim), 'code-value', 30));
		tr.appendChild(this.createTd(prevSym, 'control-indicator', 0));
		tr.appendChild(this.createTd(dim, 'code-value', 0));
		tr.appendChild(this.createTd("=", 'code-value', 0));
		tr.appendChild(this.createTd(this.formatValue(curVal, dim), 'code-value', 30));
		tr.appendChild(this.createTd(nextSym, 'control-indicator', 0));
		tr.appendChild(this.createTd(this.formatValue(nextVal, dim), 'code-value', 30));
		return tr;
	}
	MapComparisonUI.prototype.rebuildMapInfoView = function() {
		let tr = this.mapInfoTbody.firstChild;
		while( tr != undefined ) {
			let nextTr = tr.nextSibling;
			if( tr.id == "" ) this.mapInfoTbody.removeChild(tr);
			tr = nextTr;
		}
		let map = this.getCurrentMap();
		let mapFields = this.schema.classes['map generation'].fields;
		for( let f in mapFields ) {
			let fieldInfo = mapFields[f];
			if( !fieldInfo.includedInBasicInfo ) continue;
			this.mapInfoTbody.appendChild(this.createKvTr(
				ucfirst(fieldInfo.name),
				map && map[fieldInfo.jsoName] || ""
			));
		}
		
		// Build resource stats table
		clearChildren(this.mapResourceTbody);
		{
			const columnInfo = [
				{ name: "resource", attr: "resourceName" },
				{ name: "average quantity", attr: "averageQuantity", decimalPlaces: 4 },
				{ name: "total quantity", attr: "totalQuantity", decimalPlaces: 0 },
				{ name: "max unclamped probability", attr: "maxUnclampedProbability", decimalPlaces: 3 },
				{ name: "max richness", attr: "maxRichness",decimalPlaces: 3 },
			];
			let resourceHeaderTr = document.createElement('tr');
			for( let c in columnInfo ) {
				let th = document.createElement('th');
				th.appendChild(document.createTextNode(ucfirst(columnInfo[c].name)));
				resourceHeaderTr.appendChild(th);
			}
			this.mapResourceTbody.appendChild(resourceHeaderTr);
			for( let r in this.resourceNames ) {
				let resourceName = this.resourceNames[r]
				let resourceStats = map.resourceStats ? map.resourceStats[resourceName] : undefined;
				let resourceTr = document.createElement('tr');
				for( let c in columnInfo ) {
					let ci = columnInfo[c];
					let td = document.createElement('td');
					let value = ci.attr == 'resourceName' ? resourceName : resourceStats ? resourceStats[ci.attr] : undefined;
					if( ci.decimalPlaces != undefined ) {
						if( value === "+inf" || value === "-inf" ) {
							// keep it!
						} else if( value == undefined || value == "" || isNaN(value) ) {
							value = '';
						} else {
							value = (+value).toFixed(ci.decimalPlaces);
						}
						td.setAttribute('align','right');
					}
					td.appendChild(document.createTextNode(value));
					resourceTr.appendChild(td);
				}
				this.mapResourceTbody.appendChild(resourceTr);
			}
		}
		
		// Build navigation table
		clearChildren(this.mapNavigationTbody);
		let currentNode = this.mapGraph && this.mapGraph.nodes[this.currentMapKey]
		if( this.mapGraph && currentNode ) {
			let xDim = this.mapGraph.xDim;
			let yDim = this.mapGraph.yDim;
			let zDim = this.mapGraph.zDim;
			const getMapDimAttr = (map,dim) => {
				if( map == undefined ) return undefined;
				let attrs = this.mapGraph.dimensionMapAttributes[dim];
				let attrVals = [];
				for( let a in attrs ) {
					attrVals.push(map[attrs[a]]);
				}
				return attrVals.join('-');
			}
			const getNodeAttr = (nodeId,dim) => {
				if( nodeId == undefined ) return '';
				let map = this.maps[nodeId];
				return getMapDimAttr(map, dim);
			}
			if( xDim ) this.mapNavigationTbody.appendChild(this.createNavTr(
				xDim, getNodeAttr(currentNode.left, xDim), '←', getMapDimAttr(map,xDim), '→', getNodeAttr(currentNode.right, xDim)));
			if( yDim ) this.mapNavigationTbody.appendChild(this.createNavTr(
				yDim, getNodeAttr(currentNode.up  , yDim), '↑', getMapDimAttr(map,yDim), '↓', getNodeAttr(currentNode.down , yDim)));
			if( zDim ) this.mapNavigationTbody.appendChild(this.createNavTr(
				zDim, getNodeAttr(currentNode.out , zDim), '-', getMapDimAttr(map,zDim), '+', getNodeAttr(currentNode.in   , zDim)));
		}
	}
	MapComparisonUI.prototype.showMap = function(mapKey) {
		this.currentMapKey = mapKey;
		let map = this.maps[mapKey];
		let mapImageElement = this.mapImageElement;
		let mapImageUrl = this.mapImageUrl(map);
		let blankImageUrl = 'data:image/png,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8AgCQAEHwEaXsb4awAAAABJRU5ErkJggg==';
		mapImageElement.setAttribute('src',mapImageUrl)
		if( !mapImageElement.complete ) {
			// Set it to blank to make sure the old image doesn't show in the meantime.
			mapImageElement.setAttribute('src',blankImageUrl);
			setTimeout(function() { mapImageElement.setAttribute('src',mapImageUrl) }, 0);
		}
		this.rebuildMapInfoView();
		this.updateCursorCoordinates();
	};
	MapComparisonUI.prototype.updateCursorCoordinates = function() {
		let map = this.getCurrentMap();
		if( this.cursorPixelPosition == undefined || map == undefined ) {
			this.cursorPositionElement.firstChild.nodeValue = "";
			return;
		}
		let worldOffsetX = +map.mapOffsetX + (this.cursorPixelPosition.x - map.mapWidth / 2) * map.mapScale;
		let worldOffsetY = +map.mapOffsetY + (this.cursorPixelPosition.y - map.mapWidth / 2) * map.mapScale;
		if( this.cursorPositionElement ) {
			this.cursorPositionElement.firstChild.nodeValue = "" + worldOffsetX + "," + worldOffsetY;
		}
	}
	MapComparisonUI.prototype.toggleInfoOverlay = function() {
		this.infoOverlayElement.style.display = this.infoOverlayElement.style.display == 'none' ? '' : 'none';
	}
	MapComparisonUI.prototype.bump = function(message) {
		bumpAudio.load();
		bumpAudio.play();
		if( this.backgroundElement ) {
			let oldStyle = this.backgroundElement.style.background;
			this.backgroundElement.style.background = 'red';
			setTimeout(
				() => {this.backgroundElement.style.background = ""},
				100
			);
		}
		console.log(message);
	}
	MapComparisonUI.prototype.moveThroughGraph = function(direction) {
		let currentMapKey = this.currentMapKey;
		if( this.mapGraph == undefined ) {
			return this.bump("Map graph not yet loaded");
		}
		if( this.mapGraph.nodes[currentMapKey] == undefined ) {
			return this.bump("Current map key ("+currentMapKey+") not in graph!  :/");
		}
		let dimKey;
		switch( direction ) {
		case 'left': case 'right': dimKey = 'xDim'; break;
		case 'up': case 'down': dimKey = 'yDim'; break;
		case 'in': case 'out': dimKey = 'zDim'; break;
		}
		let dim = this.mapGraph[dimKey];
		let node = this.mapGraph.nodes[currentMapKey];
		let newIdx = node[direction];
		if( newIdx == undefined ) {
			return this.bump("No map "+direction+" from here ("+dimKey+" = "+dim+")");
		}
		console.log(direction+" from "+currentMapKey+" is "+newIdx);
		this.showMap(newIdx);
	};
	MapComparisonUI.prototype.onMouseMove = function( mmEvt ) {
		this.cursorPixelPosition = {
			x: (mmEvt.clientX - this.mapImageElement.offsetLeft),
			y: (mmEvt.clientY - this.mapImageElement.offsetTop)
		};
		this.updateCursorCoordinates();
	};

	const K_D = 68; const K_RIGHT = 39;
	const K_A = 65; const K_LEFT  = 37;
	const K_W = 87; const K_UP    = 38;
	const K_S = 83; const K_DOWN  = 40;
	const K_PLUS = 61;
	const K_DIFFERENT_PLUS = 187;
	const K_MINUS = 173;
	const K_DIFFERENT_MINUS = 189;
	const K_E = 69;

	MapComparisonUI.prototype.keyCodeToDirection = function( keyCode ) {
		switch( keyCode ) {
		case K_D: case K_RIGHT: return 'right';
		case K_A: case K_LEFT : return 'left';
		case K_W: case K_UP   : return 'up';
		case K_S: case K_DOWN : return 'down';
		case K_PLUS: case K_DIFFERENT_PLUS: return 'in';
		case K_MINUS: case K_DIFFERENT_MINUS: return 'out';
		default: return undefined;
		}
	}
	MapComparisonUI.prototype.onKey = function( keyEvent ) {
		if( keyEvent.getModifierState("Alt") ) return;
		
		let keyCode = keyEvent.keyCode;
		let dir = this.keyCodeToDirection(keyCode);
		if( dir != undefined ) {
			this.moveThroughGraph(dir);
			keyEvent.preventDefault();
			keyEvent.stopPropagation();
			return;
		}
		switch( keyCode ) {
		case K_E:
			this.toggleInfoOverlay();
			return;
		}
		console.log("Key event: "+keyCode);
	}
	MapComparisonUI.prototype.zoomIn = function() { this.moveThroughGraph('in'); }
	MapComparisonUI.prototype.zoomOut = function() { this.moveThroughGraph('out'); }
	MapComparisonUI.prototype.onWheel = function( wheelEvent ) {
		if( wheelEvent.deltaY > 0 ) this.zoomOut();
		if( wheelEvent.deltaY < 0 ) this.zoomIn();
		wheelEvent.preventDefault();
		wheelEvent.stopPropagation();
	}
	MapComparisonUI.prototype.start = function() {
		let bestStartingMap = undefined;
		for( let m in this.maps ) {
			bestStartingMapKey = m;
		}
		this.showMap(bestStartingMapKey);
		let mapGraphWorker = new Worker('map-graph-worker.js');
		mapGraphWorker.postMessage({
			className: "MapList",
			maps: this.maps,
		});
		window.jobTraxr.addJob({
			id: "build-graph",
			description: "Generating navigable graph...",
			isGlassy: true,
		});
		mapGraphWorker.onmessage = (e) => {
			if( e.data.className == "MapGraphGenerated" ) {
				console.log("Got the map graph!");
				window.jobTraxr.removeJob("build-graph");
				this.mapGraph = e.data.mapGraph;
				this.rebuildMapInfoView();
			} else if( e.data.className == "Progress" ) {
				console.log("Map graph worker says: "+e.data.message);
			}
		}
	}
	window.tfmpm.MapComparisonUI = MapComparisonUI;
})();
