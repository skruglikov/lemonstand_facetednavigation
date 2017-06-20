<?php

	class FacetedNavigation_FacetValue extends Db_ActiveRecord
	{
		public $table_name = 'facetednavigation_facet_values';

		protected $api_added_columns = array();
		
		public static function create()
		{
			return new self();
		}

		public function define_columns($context = null)
		{
			$front_end = Db_ActiveRecord::$execution_context == 'front-end';

			$this->define_column('name', 'Name')->validation()->fn('trim')->required();
			$this->define_column('url_name', 'URL Name')->validation()->fn('trim')->fn('mb_strtolower')->regexp('/^[0-9a-z_-]*$/i', 'URL Name can contain only latin characters, numbers and signs -, _, -')->unique('The URL Name "%s" already in use. Please select another URL Name.', array($this, 'configure_unique_validator'));
			$this->define_column('description', 'Description')->validation()->fn('trim');
			$this->define_column('option_key', 'Option Key')->validation()->fn('trim');
		}

		public function define_form_fields($context = null)
		{
			$this->add_form_field('name');
			$this->add_form_field('url_name');
			$this->add_form_field('description')->comment('Specify the value description.', 'above')->size('small');
		}

		public function validateUrl($name, $value)
		{
			$urlName = trim($this->url_name);

			if (!strlen($urlName) && !$this->page)
				$this->validation->setError('Please specify either URL name.', $name, true);
				
			return true;
		}

		public function configure_unique_validator($checker, $product, $deferred_session_key)
		{
			return true;
		}
		
		public function before_save($deferred_session_key = null) 
		{
			$this->option_key = md5($this->description);
		}

		public static function set_orders($item_ids, $item_orders)
		{
			if (is_string($item_ids))
				$item_ids = explode(',', $item_ids);
				
			if (is_string($item_orders))
				$item_orders = explode(',', $item_orders);

			$result = -1;

			foreach ($item_ids as $index=>$id)
			{
				$order = $item_orders[$index];
				if ($id == -1)
					$result = $order;

				Db_DbHelper::query('update facetednavigation_facet_values set front_end_sort_order=:front_end_sort_order where id=:id', array(
					'front_end_sort_order'=>$order,
					'id'=>$id
				));
			}

			return $result;
		}
		
		public function after_create() 
		{
			Db_DbHelper::query('update facetednavigation_facet_values set front_end_sort_order=:front_end_sort_order where id=:id', array(
				'front_end_sort_order'=>$this->id,
				'id'=>$this->id
			));

			$this->front_end_sort_order = $this->id;
		}
		
		public function after_modify($operation, $deferred_session_key)
		{
			Shop_Module::update_catalog_version();
		}
	}

?>