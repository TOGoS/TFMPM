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
		for( let attr in this.mapAttributeMetadata ) {
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
