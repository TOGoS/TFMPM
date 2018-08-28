function count(obj) {
	let c = 0;
	for( let k in obj ) ++c;
	return c;
}

function generateMapGraph(maps) {
	/**
	 * xDim: name of left/right dimension
	 * yDim: name of up/down dimension
	 * zDim: name of in/out dimension (should always be mapScale)
	 * nodes: {
	 *   map index: {
	 *     left, right, up down => index of map in the given direction
	 *     in, out => index of map in zoom in/out directions
	 *   }
	 * }
	 */
	let nodes = {};
	
	let dimensionMapAttributes = {
		commitId: ['factorioCommitId','dataCommitId'],
		mapGenSettingsUrn: ['mapGenSettingsUrn'],
		mapSeed: ['mapSeed'],
		// map gen settings should go here when I have it
		mapOffset: ['mapOffsetX'],
		mapOffsetY: ['mapOffsetY'],
		// everything else; stuff we probably don't want to navigate based on
		slopeShading: ['slopeShading'],
		mapScale: ['mapScale'],
	};

	const keyMapIgnoringDimension = function(map, ignoreDim) {
		let ignoreAttrList = dimensionMapAttributes[ignoreDim];
		let keyParts = [];
		for( let dim in dimensionMapAttributes ) {
			if( dim == ignoreDim ) continue;
			let dimAttrs = dimensionMapAttributes[dim];
			for( let a in dimAttrs ) {
				let attr = dimAttrs[a];
				keyParts.push(map[attr]);
			}
		}
		return keyParts.join('-');
	}

	const mapDimensionValueList = function(map, dim) {
		let dmas = dimensionMapAttributes[dim];
		let v = [];
		for( let a in dmas ) {
			v.push(map[dmas[a]]);
		}
		return v;
	}
	const mapDimensionValueString = function(map, dim) {
		return mapDimensionValueList(map, dim).join("-");
	}
		
	let dimensionValues = {};
	for( let mapKey in maps ) {
		let map = maps[mapKey];
		for( let dim in dimensionMapAttributes ) {
			let v = mapDimensionValueList(map, dim);
			if( dimensionValues[dim] == undefined ) dimensionValues[dim] = {};
			dimensionValues[dim][v.join('-')] = v;
		}
	}
	
	// Names of dimensions with more than one value
	let dimensionKeys = [];
	for( dim in dimensionMapAttributes ) {
		if( count(dimensionValues[dim]) > 1 ) {
			dimensionKeys.push(dim);
		}
	}
	
	let xDim = dimensionKeys[0];
	let yDim = dimensionKeys[1];
	let zDim = 'mapScale'; // Might not be in 'dimensionKeys'!
	
	/*
	 * dimension:
	 *	otherwise key:
	 *	  dimension value: map index
	 */
	let mapLists = {};

	/**
	 * Find the next map in the given direction in the given dimension
	 * that shares all other attribute values.
	 * If no such map is found but allowAltChanges is true,
	 * the next map in that direction will be searched for allowing for other values to change.
	 */
	const findNextInDirection = function( mapIndex, dim, direction, allowAltChanges ) {
		if( dim == undefined ) return undefined;
		let map = maps[mapIndex];
		let dimValue = mapDimensionValueString(map, dim);
		if( dimensionMapAttributes[dim] == undefined ) {
			throw new Error("Oh no, no dimension map attributes for dimension '"+dim+"'");
		}
		let otherwiseKey = keyMapIgnoringDimension(map, dim);
		if( mapLists[dim] == undefined ) {
			// Maybe it's fine!
			return undefined;
			//throw new Error("Oh no, no map list list for dimension '"+dim+"'");
		}
		let mapList = mapLists[dim][otherwiseKey];
		let closestDimValue = undefined;
		for( let neighborDimValue in mapList ) {
			if( neighborDimValue == dimValue ) continue;
			// TODO: Proper sorting; e.g. commit IDs should be sorted based on some custom thing
			if( direction > 0 ) {
				if( neighborDimValue > dimValue && (closestDimValue == undefined || neighborDimValue < closestDimValue) ) {
					closestDimValue = neighborDimValue;
				}
			} else if( direction < 0 ) {
				if( neighborDimValue < dimValue && (closestDimValue == undefined || neighborDimValue > closestDimValue) ) {
					closestDimValue = neighborDimValue;
				}
			} else {
				throw new Error("Ack, someone passed a non-positive/negative direction value for '"+dim+"': "+direction);
			}
		}
		if( closestDimValue != undefined ) {
			if( mapList[closestDimValue] == mapIndex ) {
				throw new Error("Somehow got the same map back ("+mapIndex+") for "+direction+" in "+dim);
			}
			return mapList[closestDimValue];
		}
		
		let closestNeighborIndex = undefined;
		if( allowAltChanges ) {
			let closestDimValue = undefined;
			for( let n in maps ) {
				if( n == mapIndex ) continue;
				let neighborDimValue = mapDimensionValueString(maps[n], dim);
				if( direction > 0 ) {
					if( neighborDimValue > dimValue && (closestDimValue == undefined || neighborDimValue < closestDimValue) ) {
						closestDimValue = neighborDimValue;
						closestNeighborIndex = n;
					}
				} else {
					if( neighborDimValue < dimValue && (closestDimValue == undefined || neighborDimValue > closestDimValue) ) {
						closestDimValue = neighborDimValue;
						closestNeighborIndex = n;
					}
				}
			}
		}
		return closestNeighborIndex;
	}
	
	for( let mapKey in maps ) {
		let map = maps[mapKey];
		for( let d in dimensionKeys ) {
			let dim = dimensionKeys[d];
			let dimVal = mapDimensionValueString(map, dim);
			if( dimensionMapAttributes[dim] == undefined ) {
				throw new Error("Oh no, no dimension map attributes for dimension '"+dim+"' (index "+d+" in key list");
			}
			let otherwiseKey = keyMapIgnoringDimension(map, dim);
			if( mapLists[dim] == undefined ) mapLists[dim] = {}
			if( mapLists[dim][otherwiseKey] == undefined ) mapLists[dim][otherwiseKey] = {}
			mapLists[dim][otherwiseKey][dimVal] = mapKey;
		}
	}
	console.log("Map lists", mapLists);
	
	for( let mapKey in maps ) {
		let node = nodes[mapKey] = {
			left: undefined, right: undefined,
			up: undefined, down: undefined,
			in: undefined, out: undefined,
		};
		node.left   = findNextInDirection(mapKey, xDim, -1, true);
		node.right  = findNextInDirection(mapKey, xDim, +1, true);
		node.up     = findNextInDirection(mapKey, yDim, -1);
		node.down   = findNextInDirection(mapKey, yDim, +1);
		node.in     = findNextInDirection(mapKey, zDim, -1);
		node.out    = findNextInDirection(mapKey, zDim, +1);
	}
	
	return Promise.resolve({
		dimensionMapAttributes,
		xDim, yDim, zDim,
		nodes
	});
}

addEventListener("message", (e) => {
	if( e.data.className == "MapList" ) {
		postMessage({
			className: "Progress",
			message: "Hello!",
		});
		generateMapGraph(e.data.maps).then( (mapGraph) => {
			postMessage({className: "MapGraphGenerated", mapGraph });
		});
	}
});
