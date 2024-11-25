jQuery(document).ready(function(){
   jQuery('#bt-map').click(function() 
   {
      const titre = jQuery('#Mg-title').val().trim();
      const lat = jQuery('#Mg-latitude').val().trim();
      const lon = jQuery('#Mg-longitude').val().trim();
 
      if (titre == '') 
      {
	     jQuery('#Mg-title-error').show();        
      } 
      else 
      { 
	      jQuery('#Mg-title-error').hide();
      } 

      if (lat == '')
      { 
	     jQuery('#Mg-latitude-error').show();         
      }
      else 
      {
	     jQuery('#Mg-latitude-error').hide();
      } 
      
      if (lon == '') 
      { 
		 jQuery('#Mg-longitude-error').show();	    
      } 
      else 
      {
	     jQuery('#Mg-longitude-error').hide();
      }
      
      if ( (titre != '') && (lat != '') && (lon != '')) 
      {
	     jQuery('form').submit();
      } 
      else 
      {
		 return false;
      } 
  }); 

  
   jQuery('#bt-delete').click(function() 
   {
      if (confirm("Etes-vous sûr de vouloir supprimer cette carte ?")) 
      {
	      jQuery('form').submit();
      }
      
      return false;
   }); 	
   
  jQuery("#codemap").click(function(){
      this.select();
  });
});