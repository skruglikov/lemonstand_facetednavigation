<?
	class FacetedNavigation_Settings extends Backend_Controller
	{
		public $implement = 'Db_ListBehavior, Db_FormBehavior';
		public $list_model_class = 'FacetedNavigation_Facet';
		public $list_record_url = null;

		public $form_preview_title = 'Faceted Navigation';
		public $form_create_title = 'New Facet';
		public $form_edit_title = 'Edit Facet';
		public $form_model_class = 'FacetedNavigation_Facet';
		public $form_not_found_message = 'Option set not found';
		public $form_redirect = null;

		public $form_edit_save_flash = 'The faceted navigation facet has been successfully saved';
		public $form_create_save_flash = 'The faceted navigation facet has been successfully added';
		public $form_edit_delete_flash = 'The faceted navigation facet has been successfully deleted';

		protected $required_permissions = array('shop:manage_shop_settings');

		protected $globalHandlers = array(
			'onLoadFacetValueForm',
			'onSaveFacetValue',
			'onUpdateFacetValuesList',
			'onDeleteFacetValue',
			'onDeleteFacetSelected',
			'onSetFacetsOrders',
			'onSetFacetValuesOrders'
		);

		public function __construct()
		{
			parent::__construct();
			$this->app_tab = 'shop';
			$this->app_page = 'products';
			$this->app_module_name = 'Faceted Navigation';

			$this->list_record_url = url('/facetednavigation/settings/edit/');
			$this->form_redirect = url('/facetednavigation/settings');
		}

		public function index()
		{
			$this->app_page_title = 'Faceted Navigation Sets';
		}
		
		public function listGetRowClass($model)
		{
			if ($model instanceof FacetedNavigation_Facet)
				return $model->is_disabled ? 'disabled' : null;
		}

		protected function onLoadFacetValueForm()
		{
			try
			{
				$this->resetFormEditSessionKey();

				$id = post('facet_value_id');
				$value = $id ? FacetedNavigation_FacetValue::create()->find($id) : FacetedNavigation_FacetValue::create();
				if (!$value)
					throw new Phpr_ApplicationException('Value not found');

				$value->define_form_fields();

				$this->viewData['value'] = $value;
				$this->viewData['session_key'] = post('edit_session_key');
				$this->viewData['value_id'] = post('facet_value_id');
				$this->viewData['trackTab'] = false;
			}
			catch (Exception $ex)
			{
				$this->handlePageError($ex);
			}

			$this->renderPartial('facet_value_form');
		}

		protected function onSaveFacetValue($set_id)
		{
			try
			{
				$id = post('value_id');

				$value = $id ? FacetedNavigation_FacetValue::create()->find($id) : FacetedNavigation_FacetValue::create();

				if (!$value)
					throw new Phpr_ApplicationException('Value not found');
					
				if (!$id)
					Backend::$events->fireEvent('core:onBeforeFormRecordCreate', $this, $value);
				else
					Backend::$events->fireEvent('core:onBeforeFormRecordUpdate', $this, $value);

				$facet = $this->getFacetObj($set_id);

				$value->init_columns_info();
				$value->define_form_fields();
				$value->option_in_set = 1;
				$value->save(post('FacetedNavigation_FacetValue'), $this->formGetEditSessionKey());
				
				if (!$id)
					Backend::$events->fireEvent('core:onAfterFormRecordCreate', $this, $value);
				else
					Backend::$events->fireEvent('core:onAfterFormRecordUpdate', $this, $value);

				if (!$id)
					$facet->facet_values->add($value, post('set_session_key'));
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}

		protected function onUpdateFacetValuesList($parentId = null)
		{
			try
			{
				$this->viewData['form_model'] = $this->getFacetObj($parentId);
				$this->renderPartial('facet_values_list');
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}

		protected function onDeleteFacetSelected()
		{
			$deleted = 0;

			$ids = post('list_ids', array());
			$this->viewData['list_checked_records'] = $ids;

			foreach ($ids as $id)
			{
				$facet = null;
				try
				{
					$facet = FacetedNavigation_Facet::create()->find($id);
					if (!$facet)
						throw new Phpr_ApplicationException('Facet with identifier '.$id.' not found.');

					$facet->delete();
					$deleted++;
				}
				catch (Exception $ex)
				{
					if (!$facet)
						Phpr::$session->flash['error'] = $ex->getMessage();
					else
						Phpr::$session->flash['error'] = 'Error deleting facet "'.$facet->name.'": '.$ex->getMessage();

					break;
				}
			}

			if ($deleted)
				Phpr::$session->flash['success'] = 'Facet deleted: '.$deleted;

			$this->renderPartial('settings_page_content');
		}

		protected function onDeleteFacetValue($parentId = null)
		{
			try
			{
				$facet = $this->getFacetObj($parentId);
				$id = post('facet_value_id');

				$value = $id ? FacetedNavigation_FacetValue::create()->find($id) : FacetedNavigation_FacetValue::create();
				
				if ($value)
				{
					$facet->facet_values->delete($value, $this->formGetEditSessionKey());
				}

				$this->viewData['form_model'] = $facet;
				$this->renderPartial('facet_values_list');
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}

		protected function onSetFacetsOrders($parent_id)
		{
			try
			{
				FacetedNavigation_Facet::set_orders(post('item_ids'), post('sort_orders'));
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}

		protected function onSetFacetValuesOrders($parent_id)
		{
			try
			{
				FacetedNavigation_FacetValue::set_orders(post('item_ids'), post('sort_orders'));
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}

		private function getFacetObj($id)
		{
			return strlen($id) ? $this->formFindModelObject($id) : $this->formCreateModelObject();
		}

		protected function onLoadMarsForm()
		{
			try
			{
				$id = post('marsian_id');
				$marsian = $id ? FacetedNavigation_Facet::create()->find($id) : FacetedNavigation_Facet::create();
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
	}

?>