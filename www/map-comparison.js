(function() {
	if( typeof window.tfmpm == 'undefined' ) window.tfmpm = {};
	
	function clearChildren(elem) {
		while(elem.firstChild) {
			elem.removeChild(elem.firstChild);
		}
	}
	
	var MapComparisonUI = function(params) {
		this.maps = params.maps;
		this.mapImageElement = params.mapImageElement;
		this.mapInfoTbody = params.mapInfoTbody;
		this.mapNavigationTbody = params.mapNavigationTbody;
		this.cursorPositionElement = params.cursorPositionElement;
		this.backgroundElement = params.backgroundElement;
		this.currentMapKey = undefined;
		this.uriResolverPrefix = "uri-res/raw/";
		this.mapAttributeMetadata = [];
		this.cursorPixelPosition = undefined;
		let keyedMapAttributeMetadata = {};
		for( let m in this.maps ) {
			let map = this.maps[m];
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
		td.appendChild(document.createTextNode(value));
		if( className ) td.className = className;
		if( widthPercentage != undefined ) td.setAttribute('width', widthPercentage+'%');
		return td;
	}
	MapComparisonUI.prototype.createSeparatorTr = function() {
		let tr = document.createElement('tr');
		tr.className = "separator";
		let td = document.createElement('td');
		td.setAttribute('colspan','2');
		let hr = document.createElement('hr');
		td.appendChild(hr);
		tr.appendChild(td);
		return tr;
	}
	MapComparisonUI.prototype.createKvTr = function(k, v, kClassName) {
		let tr = document.createElement('tr');
		let kTd = document.createElement('td');
		tr.appendChild(this.createTd(k, kClassName));
		tr.appendChild(this.createTd(v, 'code-value'));
		return tr;
	}
	MapComparisonUI.prototype.createNavTr = function(dim, prevVal, prevSym, curVal, nextSym, nextVal) {
		let tr = document.createElement('tr');
		tr.appendChild(this.createTd(prevVal, 'code-value', 30));
		tr.appendChild(this.createTd(prevSym, 'control-indicator', 0));
		tr.appendChild(this.createTd(dim, 'code-value', 0));
		tr.appendChild(this.createTd("=", 'code-value', 0));
		tr.appendChild(this.createTd(curVal, 'code-value', 30));
		tr.appendChild(this.createTd(nextSym, 'control-indicator', 0));
		tr.appendChild(this.createTd(nextVal, 'code-value', 30));
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
		this.mapInfoTbody.appendChild(this.createSeparatorTr());
		for( let attrIndex in this.mapAttributeMetadata ) {
			let attr = this.mapAttributeMetadata[attrIndex];
			this.mapInfoTbody.appendChild(this.createKvTr(
				attr.code,
				map && map[attr.code] || ""
			));
		}
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
		this.mapImageElement.setAttribute('src',this.mapImageUrl(map));
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
	MapComparisonUI.prototype.bump = function(message) {
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
	MapComparisonUI.prototype.keyCodeToDirection = function( keyCode ) {
		const K_D = 68; const K_RIGHT = 39;
		const K_A = 65; const K_LEFT  = 37;
		const K_W = 87; const K_UP    = 38;
		const K_S = 83; const K_DOWN  = 40;
		const K_PLUS = 61;
		const K_MINUS = 173;
		switch( keyCode ) {
		case K_D: case K_RIGHT: return 'right';
		case K_A: case K_LEFT : return 'left';
		case K_W: case K_UP   : return 'up';
		case K_S: case K_DOWN : return 'down';
		case K_PLUS: return 'in';
		case K_MINUS: return 'out';
		default: return undefined;
		}
	}
	MapComparisonUI.prototype.onKey = function( keyEvent ) {
		let keyCode = keyEvent.keyCode;
		let dir = this.keyCodeToDirection(keyCode);
		if( dir != undefined ) {
			this.moveThroughGraph(dir);
			keyEvent.preventDefault();
			keyEvent.stopPropagation();
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
		mapGraphWorker.onmessage = (e) => {
			if( e.data.className == "MapGraphGenerated" ) {
				console.log("Got the map graph!");
				this.mapGraph = e.data.mapGraph;
				this.rebuildMapInfoView();
			} else if( e.data.className == "Progress" ) {
				console.log("Map graph worker says: "+e.data.message);
			}
		}
	}
	window.tfmpm.MapComparisonUI = MapComparisonUI;
})();
