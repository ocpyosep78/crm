<?php

	class Snippet_hnd_lists extends Snippets_Handlers_Commons{
		
		/**
		 * @overview: commonList at first just prints the frame in which the
		 *            actual list will be loaded, and its initialize function
		 *            will request the list to be loaded (innerCommonList)
		 *            from the client, as soon as the frame is ready.
		 */
		protected function handle_commonList(){
		
//			$this->filterToolsForThisElement( 'create' );
		
			return $this->fetch( 'lists/commonList' );
			
		}
		
		/**
		 * @overview: this is the actual list that goes within a commonList.
		 *            There's nothing against calling it on its own as any
		 *            regular element (to present it without the frame or in
		 *            another frame, maybe)
		 */
		protected function handle_innerCommonList(){
			
			$params = $this->params;
			
			# This snippet doesn't include a create button
			$this->hideTools('create');
			
			# Get Data
			$data = $this->Source->getData('list', $params['filters']);
			$this->assign('data', $data);
			
			return $this->fetch( 'lists/innerCommonList' );
		
		}
		
		protected function handle_simpleList(){
		
			return $this->fetch( 'lists/simpleList' );
			
		}
		
	}

?>