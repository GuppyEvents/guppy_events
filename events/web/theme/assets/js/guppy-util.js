/**
 *
 * Projede kullanılan genel utility yeteneklerini sağlar
 * 
 * @module  GuppyUtil
 */
var GuppyUtil = (function() {

	/**
	 * Bir mail adresinini valid olup olmadığını döner.
	 * 
	 * @param  {string} mail address
	 * @return {bool} 
	 */
	var validateEmail = function(email) {

		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(email);
	}

	return {

		validateEmail:validateEmail
	}
})();