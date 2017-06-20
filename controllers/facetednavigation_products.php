<?
	class FacetedNavigation_Products extends Backend_Controller
	{
		public $implement = 'Db_ListBehavior, Db_FormBehavior';

		public $form_edit_save_flash = 'The facet has been successfully saved';
		public $form_create_save_flash = 'The facet has been successfully added';
		public $form_edit_delete_flash = 'The facet has been successfully deleted';

		public function __construct()
		{
			$this->globalHandlers[] = 'onLoadMarsForm';
			$this->globalHandlers[] = 'onUpdateMarsianValues';
			$this->globalHandlers[] = 'onUpdateMarsianValue';
			$this->globalHandlers[] = 'onUpdateMarsianList';
			$this->globalHandlers[] = 'onAddMarsian';
			$this->globalHandlers[] = 'onDeleteMarsian';
			parent::__construct();
		}

		private function getProductObj($id)
		{
			return strlen($id) ? $this->formFindModelObject($id) : $this->formCreateModelObject();
		}

		protected function onLoadMarsForm()
		{
			try
			{
				$id = post('marsian_id');
				$marsian = $id ? FacetedNavigation_Product::create()->find($id) : FacetedNavigation_Product::create();
				if (!$marsian)
					throw new Phpr_ApplicationException('Mars not found');

				$marsian->define_form_fields();

				$this->viewData['marsian'] = $marsian;
				$this->viewData['session_key'] = post('edit_session_key');
				$this->viewData['marsian_id'] = post('marsian_id');
				$this->viewData['trackTab'] = false;
			}
			catch (Exception $ex)
			{
				$this->handlePageError($ex);
			}

			$this->renderPartial('marsian_form');
		}

		protected function onUpdateMarsianValues()
		{
			$marsian = FacetedNavigation_Product::create();
			$marsian->define_form_fields();
			$data = post('FacetedNavigation_Product', array());
			$marsian->shop_attribute_id = $data['shop_attribute_id'];

			$this->formRenderFieldContainer($marsian, 'value_pickup');
		}

		protected function onUpdateMarsianValue()
		{
			$marsian = FacetedNavigation_Product::create();
			$marsian->define_form_fields();
			$data = post('FacetedNavigation_Product', array());
			$marsian->load_value($data['value_pickup']);

			$this->formRenderFieldContainer($marsian, 'value');
		}
		
		protected function onUpdateMarsianList($parentId = null)
		{
			try
			{
				$this->viewData['form_model'] = $this->getProductObj($parentId);
				$this->renderPartial('mars_list');
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}				

		protected function onAddMarsian($parentId = null)
		{
			$data = post('FacetedNavigation_Product');
			$data['attribute_value_id'] = $data['value_pickup'];
			
			try
			{
				$id = post('marsian_id');

				$marsian = $id ? FacetedNavigation_Product::create()->find($id) : FacetedNavigation_Product::create();
				if (!$marsian)
					throw new Phpr_ApplicationException('Option not found');

				$product = $this->getProductObj($parentId);
				$marsian->init_columns_info();
				$marsian->define_form_fields();

				$marsian->save($data, $this->formGetEditSessionKey());

				if (!$id)
					$product->mars->add($marsian, post('product_session_key'));
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}

		protected function onDeleteMarsian($parentId = null)
		{
			try
			{
				$product = $this->getProductObj($parentId);

				$id = post('marsian_id');
				$marsian = $id ? FacetedNavigation_Product::create()->find($id) : FacetedNavigation_Product::create();

				if ($marsian)
					$product->mars->delete($marsian, $this->formGetEditSessionKey());

				$this->viewData['form_model'] = $product;
				$this->renderPartial('mars_list');
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
	}
?>
