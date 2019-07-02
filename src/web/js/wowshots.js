var app = angular.module('wowShotsApp', ['angularMoment']);
app.controller('wowShotsController', ["$http", "$interval", "moment", 
		function( $http, $interval, moment ) {
	var vm = this;

	vm.secondsList = [0.1, 0.2, 0.3, 0.4, 0.5, 0.8, 1, 2, 5];
	vm.timeOut = 0.8;
	vm.widthList = [320, 640, 720, 1000, 1200, 1440];
	vm.imgWidth = 640;
	limitToSeedValues = [1, 2, 5];
	vm.imgLimit = 10;

	vm.imgsToDisplay = {};
	vm.curSlide = 0;

	makeLimitValues = function() {
	}

	loadData = function() {
		$http.get('json.php')
			.then( function( response ) {
				vm.wowShots = response.data.wowshots;
				vm.dateFilterValues = response.data.filterData;

				// calculate the limit to choice array
				vm.limitToValues = [];

				p = 0;
				lcv = 0;
				v = limitToSeedValues[lcv] * Math.pow( 10, p );
				while( v < vm.wowShots.length ) {
					vm.limitToValues.push( v );
					lcv++;
					if ( lcv >= limitToSeedValues.length ) {
						lcv = 0;
						p++;
					}
					v = limitToSeedValues[lcv] * Math.pow( 10, p );
				}
				vm.limitToValues.push( vm.wowShots.length );
				if (vm.imgLimit > vm.wowShots.length) {
					vm.imgLimit = vm.wowShots.length;
				}

				vm.isLoaded = true;
				applyFilter( vm.wowShots );

				vm.startCarousel();
			})
	};

	var reload = $interval( function() {
		console.log("Reloading data");
		loadData();
	}, 300000); // every 5 minutes should do   ---   300000

	imgFilterFunction = function( imgData ) {
		console.log( "imgData: " + imgData.ts );
		if ( angular.isDefined( vm.dateFilter ) ) {
			//console.log( "vm.dateFilter.minTS: " + vm.dateFilter.minTS );
			//console.log( vm.dateFilter.minTS <= imgData.ts && "Greater than MinTS" || "too old" );
			//console.log( imgData.ts <= vm.dateFilter.maxTS && "Less than MaxTS" || "too young" );
			return vm.dateFilter.minTS <= imgData.ts && imgData.ts <= vm.dateFilter.maxTS;
		}
		return true;
	};

	var applyFilter = function( obj ) {
		console.log("applyFilter");
		vm.imgsToDisplay = obj.slice(-vm.imgLimit).filter( imgFilterFunction );
	};

	vm.setLimit = function( newLimit ) {
		vm.imgLimit = newLimit;
		applyFilter( vm.wowShots );
	};

/*
	buildDateFilters = function( imgList ) {
		// 
		if (! angular.isDefined( imgList ) ) return;
		valsOut = [];
		prevKey = "";
		index = 0;
		
		for( i in imgList ) {
			console.log( imgList[i] );
			dateKey = moment(new Date(imgList[i].ts*1000)).format('ddd D MMM YYYY');
			if ( dateKey != dateKey ) {
				prevKey = dateKey;
				index++;
			}
			console.log(dateKey);
		}

		return valsOut;
	};
*/
	var carousel;
	vm.startCarousel = function() {
		if ( angular.isDefined( carousel ) ) return;

		carousel = $interval( function() {
			//console.log("Next slide of "+vm.imgLimit+" at timeout: "+vm.timeOut);
			if ( vm.curSlide >= vm.imgsToDisplay.length ) vm.curSlide = 0;
			vm.slideCount = vm.imgsToDisplay.length;
			
			curImg = vm.imgsToDisplay[vm.curSlide];
			vm.imgPath = curImg.path;
			vm.thumbPath = "mythumb.php?fname="+vm.imgPath+"&w="+vm.imgWidth;
			vm.imgTS = curImg.ts;
			
			vm.curSlide++;
		}, vm.timeOut * 1000);
	};

	vm.resetCarousel = function() {
		if (angular.isDefined(carousel)) {
			$interval.cancel(carousel);
			carousel = undefined;
		}
		vm.startCarousel();
	};
		
	loadData();
}]);

