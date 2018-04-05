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
		this.uriResolverPrefix = "uri-res/raw/";
		this.mapAttributeMetadata = [];
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
	MapComparisonUI.prototype.mapImageUrl = function(map) {
		return this.uriResolverPrefix + map.mapImageUrn + "/" + map.generationId + ".png";
	};
	MapComparisonUI.prototype.showMap = function(map) {
		this.mapImageElement.setAttribute('src',this.mapImageUrl(map));
		clearChildren(this.mapInfoTbody);
		for( let attrIndex in this.mapAttributeMetadata ) {
			let attr = this.mapAttributeMetadata[attrIndex];
			let attrTr = document.createElement('tr');
			let attrKeyTd = document.createElement('td');
			attrKeyTd.appendChild(document.createTextNode(attr.code));
			let attrValueTd = document.createElement('td');
			attrValueTd.appendChild(document.createTextNode(map[attr.code]));
			attrTr.appendChild(attrKeyTd);
			attrTr.appendChild(attrValueTd);
			this.mapInfoTbody.appendChild(attrTr);
		}
	};
	MapComparisonUI.prototype.start = function() {
		let bestStartingMap = undefined;
		for( let m in this.maps ) {
			bestStartingMap = this.maps[m];
		}
		this.showMap(bestStartingMap);
	}
	window.tfmpm.MapComparisonUI = MapComparisonUI;
})();
