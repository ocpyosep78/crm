<div class='Modules_Element Wrapper_of_Type_{$name}'>
	<form name='{$name}'>
    	<input type='hidden' name='type' value='{$type}' />
    	<input type='hidden' name='code' value='{$code}' />
    	<input type='hidden' name='modifier' value='{$modifier}' />
    	<input type='hidden' name='params' value='{$params}' />
    </form>
    {include file=$name}
</div>