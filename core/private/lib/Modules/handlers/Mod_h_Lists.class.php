<?php
	
	/**
	 * 
		 *            (notice this class descends from ModulesBase and has
		 *            access to its properties $type, $code and $modifier)
	 */

	class Mod_h_Lists extends ModulesBase{
		
		/**
		 * @overview: commonList at first just prints the frame in which the
		 *            actual list will be loaded, and its initialize function
		 *            will request the list to be loaded (innerCommonList)
		 *            from the client, as soon as the frame is ready.
		 */
		protected function commonList(){
		
			$this->filterToolsForThisElement( 'create' );
		
			return $this->fetch( 'lists/commonList' );
			
		}
		
		/**
		 * @overview: this is the actual list that goes within a commonList.
		 *            There's nothing against calling it on its own as any
		 *            regular element (to present it without the frame or in
		 *            another frame, maybe)
		 */
		protected function innerCommonList(){
			
			# Get Data
			$src = !empty($this->params['src']) ? $this->params['src'] : 'common';
			$data = $this->getListData($src, $this->params['filters']);
			$this->TemplateEngine->assign('data', $data);
			
/*			oSmarty()->assign('axns', $static['actions']);
			oSmarty()->assign('tools', $static['tools']); */
			
			return $this->fetch( 'lists/innerCommonList' );
		
		}
		
		protected function simpleList(){
		
			return $this->fetch( 'lists/simpleList' );
			
		}
	
	}

?>