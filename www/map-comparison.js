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
		this.cursorPositionElement = params.cursorPositionElement;
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
	}
	MapComparisonUI.prototype.getCurrentMap = function() {
		if( this.currentMapKey == undefined ) return undefined;
		return this.maps[this.currentMapKey];
	}
	MapComparisonUI.prototype.mapImageUrl = function(map) {
		return this.uriResolverPrefix + map.mapImageUrn + "/" + map.generationId + ".png";
	};
	MapComparisonUI.prototype.rebuildMapInfoView = function() {
		let tr = this.mapInfoTbody.firstChild;
		while( tr != undefined ) {
			let nextTr = tr.nextSibling;
			if( tr.id == undefined ) this.mapInfoTbody.removeChild(tr);
			tr = nextTr;
		}
		let map = this.getCurrentMap();
		for( let attrIndex in this.mapAttributeMetadata ) {
			let attr = this.mapAttributeMetadata[attrIndex];
			let attrTr = document.createElement('tr');
			let attrKeyTd = document.createElement('td');
			attrKeyTd.appendChild(document.createTextNode(attr.code));
			let attrValueTd = document.createElement('td');
			attrValueTd.appendChild(document.createTextNode(map && map[attr.code] || ""));
			attrTr.appendChild(attrKeyTd);
			attrTr.appendChild(attrValueTd);
			this.mapInfoTbody.appendChild(attrTr);
		}
	}
	MapComparisonUI.prototype.showMap = function(mapKey) {
		this.currentMapKey = mapKey;
		let map = this.maps[mapKey];
		this.mapImageElement.setAttribute('src',this.mapImageUrl(map));
		this.rebuildMapInfoView();
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
	MapComparisonUI.prototype.onMouseMove = function( mmEvt ) {
		this.cursorPixelPosition = {
			x: (mmEvt.clientX - this.mapImageElement.offsetLeft),
			y: (mmEvt.clientY - this.mapImageElement.offsetTop)
		};
		this.updateCursorCoordinates();
	};
	MapComparisonUI.prototype.onKey = function( mmEvt ) { }
	MapComparisonUI.prototype.onWheel = function( mmEvt ) { }
	MapComparisonUI.prototype.start = function() {
		let bestStartingMap = undefined;
		for( let m in this.maps ) {
			bestStartingMapKey = m;
		}
		this.showMap(bestStartingMapKey);
		
	}
	window.tfmpm.MapComparisonUI = MapComparisonUI;
})();
