function updateTotalAmount($betting_id,$bonus){
	var totalAmount = parseInt($('#betting-amount-'+$betting_id).val())+parseInt($bonus);
	if(isNaN(totalAmount)){
		$('#betting-total-'+$betting_id).text('');		
	}else{
		$('#betting-total-'+$betting_id).text(totalAmount);
	}
}

function filterBettings(){
	$('#betting-filter-form').submit();
}