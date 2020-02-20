(function(){

	//
	var _stool_debug = {
		errors: [],
		init: function(){
			this.errors = _stool_debug;
			for(var error in this.errors){
				console.log(error);
			}
		}
	};

})();