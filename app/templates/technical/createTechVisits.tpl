{literal}
<!-- IE wont' understand conditional comment without some commented output before...
	even if it's '&nbsp;' (amazing crap, isn't it?!) -->
<!--[if IE]>&nbsp;
	<style type='text/css'>
		#technicalForm INPUT{ background:url(app/images/background/transparent.gif) repeat !important; }
		#tch_custNumber{ top:125px !important; }
		#tch_info_left INPUT{ height:18px; }
		.tch_options{ right:30px; }
		.tch_options INPUT{ margin:-3px 0px 0px 35px !important; padding:0px !important; }
		#tch_installDate{ top:203px; right:15px; }
		#tch_complete{ position:relative; top:8px; left:20px !important; }
		#tch_complete INPUT{ margin:-21px 12px 0px 16px !important; }
		.tch_costLine INPUT{ top:0px !important; }
		#tch_pendingEstimate{ top:0px !important; }
		#tch_period{ background:url(app/images/background/transparent.gif) !important; }
		.tch_quality INPUT{ margin:3px 26px 0px 62px !important; }
		.tch_quality #tch_quality_good{ margin-left:51px !important; }
		.tch_quality #tch_quality_excellent{ margin-left:69px !important; }
		#tch_period{ top:506px !important; }
		#tch_period INPUT{ height:16px !important; }
	</style>
<![endif]-->
{/literal}


<form name='frm_newTechVisit' action='javascript:void(0);'>
  <div id='technicalForm'>

	<img id='technicalFormBg' src='app/images/technical/form.jpg' alt='' />
  
	<div id='tch_buttons'>
		<div id='tch_save'>Guardar</div>
		<div id='tch_print'>Imprimir</div>
	</div>
	
	<input type='text' id='tch_number' maxlength='8' value='' />
	
	<div id='tch_extra'>
		<div>Técnico responsable</div>
		<select id='tch_technician'>
		  <option value=''></option>
		  {foreach from=$technicians key=k item=v}<option value='{$k}'>{$v}</option>{/foreach}
		</select>
	</div>
	
	<div id='tch_date'>
		<input type='text' id='tch_day' maxlength='2' value='' />
		<input type='text' id='tch_month' maxlength='2' value='' />
		<input type='text' id='tch_year' maxlength='4' value='' />
	</div>
	
	<div id='tch_info_left'>
		<input type='text' id='tch_customer' maxlength='120' value='' />
		<input type='text' id='tch_contact' maxlength='120' value='' />
		<input type='text' id='tch_address' maxlength='120' value='' />
		<input type='text' id='tch_phone' maxlength='15' value='' />
	</div>
	
	<input type='text' id='tch_custNumber' maxlength='8' value='' />
	
	<div id='tch_subscribed' class='tch_options'>
		<input type='radio' name='subscribed' value='1' disabled='disabled' />
		<input type='radio' name='subscribed' value='0' disabled='disabled' />
	</div>
	<div id='tch_warranty' class='tch_options'>
		<input type='radio' name='warranty' value='1' disabled='disabled' />
		<input type='radio' name='warranty' value='0' disabled='disabled' />
	</div>
	<div id='tch_installDate'>
		<input type='text' id='tch_installDay' maxlength='2' value='--' disabled='disabled' />
		<input type='text' id='tch_installMonth' maxlength='2' value='--' disabled='disabled' />
		<input type='text' id='tch_installYear' maxlength='4' value='--' disabled='disabled' />
	</div>
	
	<div id='tch_body'>
	  <div class='tch_row'>
		<select id='tch_id_system'>
		  <option value=''></option>
		  {foreach from=$systems key=k item=v}<option value='{$k}'>{$v}</option>{/foreach}
		</select>
	  </div>
	  <div class='tch_row'>
		<input type='text' id='tch_reason' value='' />
	  </div>
	  <div class='tch_row'>
		<input type='text' id='tch_outcome' value='' />
	  </div>
	  <div class='tch_row'>
		<div id='tch_complete'>
			<input type='radio' name='complete' value='1' />
			<input type='radio' name='complete' value='0' />
		</div>
		<input type='text' id='tch_ifIncomplete' value='' />
	  </div>
	  <div class='tch_row'>
		<input type='text' id='tch_usedProducts' value='' />
	  </div>
	  <div class='tch_row tch_costLine'>
		<input type='text' id='tch_cost' value='' />
		<input type='text' id='tch_costDollars' value='' />
		<input type='text' id='tch_invoice' value='' />
	  </div>
	  <div class='tch_row'>
		<input type='text' id='tch_order' value='' />
		<input type='checkbox' id='tch_pendingEstimate' value='1' />
	  </div>
	  <div class='tch_row tch_quality'>
		<input type='radio' name='quality' value='bad' />
		<input type='radio' name='quality' value='regular' />
		<input type='radio' name='quality' id='tch_quality_good' value='good' />
		<input type='radio' name='quality' id='tch_quality_excellent' value='excellent' />
	  </div>
	</div>
	
	<div id='tch_period'>
		<input type='text' id='tch_startsH' maxlength='2' value='' />
		<input type='text' id='tch_startsM' maxlength='2' value='' />
		<input type='text' id='tch_endsH' maxlength='2' value='' />
		<input type='text' id='tch_endsM' maxlength='2' value='' />
	</div>

	<img class='tchSearch' id='tchSrch_custNumber' src='app/images/buttons/search.gif' alt='' />
	<img class='tchSearch' id='tchSrch_customer' src='app/images/buttons/search.gif' alt='' />
	<img class='tchSearch' id='tchSrch_contact' src='app/images/buttons/search.gif' alt='' />
	<img class='tchSearch' id='tchSrch_address' src='app/images/buttons/search.gif' alt='' />
	<img class='tchSearch' id='tchSrch_phone' src='app/images/buttons/search.gif' alt='' />
  
	<input type='hidden' id='tch_onSale' value='' />
	<input type='hidden' id='tch_id_customer' value='' />
	<input type='hidden' id='tch_id_sale' value='' />
  
	<input type='submit' class='button' id='tch_submit' value='Guardar' />
	
  </div>
</form>

<div>
	<div id='tch_suggest_title'>
		Ingrese el número de Cliente para ver las instalaciones correspondientes
	</div>
	<div id='tch_suggest'></div>
</div>