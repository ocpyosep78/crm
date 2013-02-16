{literal}
<style type="text/css">
	.loginWrapper{
		position:absolute;
		top:50%;
		left:50%;
		height:150px;
		width:350px;
		margin-top:-110px;
		margin-left:-175px;
		text-align:center;
	}
	#loginBg{
		padding:0px;
		opacity:0.85;
		filter:alpha(opacity=85);
	}
	#loginTbl{
		margin:auto;
		margin-top:27px;
	}
	#loginTbl TH{
		font-weight:normal;
		font-family:Georgia, "Times New Roman", Times, serif;
		font:"Times New Roman", Times, serif;
		text-align:right;
	}
	.specialButton{
		height:25px;
		width:120px;
		margin-top:15px;
		padding:2px 20px;
		background:#000030;
		color:#ffffff;
		cursor:pointer;
		opacity:0.8;
		filter:alpha(opacity=80);
	}
</style>

<script type="text/javascript">
	window.addEvent('domready', function(){
		var oForm=$(document.forms.formLogin), oUser=$(oForm.user), oPass=$(oForm.pass);
		oUser.onfocus = oPass.onfocus = function(e){ highLightBox(this); };
		oForm.addEvent('submit', attemptLogin);
		oUser.addEvent('keypress', function(e){
			if( e.key == 'enter' && oUser.value !== '' && oPass.value === '' ){
				oPass.focus();
				e.preventDefault();
			};
		});
		oUser.focus();
		function attemptLogin(){
			if( oUser.value === '' ){
				oUser.focus();
				return showStatus('Debe escribir un nombre de usuario.');
			}
			else if( !oPass.value ){
				oPass.focus();
				return showStatus('Debe escribir su contraseña.');
			}
			else xajax_login(oUser.value, oPass.value);
			return false;
		};
	} );
</script>
{/literal}


<div class='loginWrapper' id='loginBg'></div>

<div class='loginWrapper'>
	<form name='formLogin' action='javascript:void(0);'>

	  <table id='loginTbl' border="0" align="center">
		<tr>
			<th>Usuario:</th>
			<td><input class='input' type="text" name="user" /></td>
		</tr>
		<tr>
			<th>Contraseña:</th>
			<td><input class='input' type="password" name="pass" /></td>
		</tr>
		<tr align="center">
			<td colspan="2">
				<input type="submit" id='btn_login' class="specialButton" value="Entrar" />
			</td>
		</tr>
	  </table>

	</form>
</div>