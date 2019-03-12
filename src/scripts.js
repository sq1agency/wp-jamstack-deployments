jQuery(function(){
	var searchParams = new URLSearchParams(window.location.search);
	if (searchParams.get('jamstack-deploy-status') == 'started-deploying'){
		//localStorage.setItem("jamstack-deploy-status", 'started-deploying');
		jQuery('.jamstack-admin-deploy-started').show();
		jQuery('.jamstack-admin-deploy').hide();
		jQuery('.jamstack-form').hide();
	}
});
