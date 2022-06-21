
$(".case").click(function(e) {
  address = $(this).find(".adresse");
  if(address) {
  	address.toggle();
  	address.css('visibility', function(i, visibility) {
        return (visibility == 'visible') ? 'hidden' : 'visible';
    });
  }
  
  infos = $(this).find(".frontCase");
  if(infos) {
  	infos.toggle();
  	infos.css('visibility', function(i, visibility) {
        return (visibility == 'visible') ? 'hidden' : 'visible';
    });
  }
});

