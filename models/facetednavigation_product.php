<?php

	class FacetedNavigation_Product extends Db_ActiveRecord
	{
		public $table_name = 'facetednavigation_products';

		public $implement = 'Db_Sortable';
		public $custom_columns = array('value_pickup'=>db_text);

		public $belongs_to = array(
			'product_facet'=>array('class_name'=>'FacetedNavigation_Facet', 'foreign_key'=>'facet_id'),
			'facet_value'=>array('class_name'=>'FacetedNavigation_FacetValue', 'foreign_key'=>'value_id')
		);

		public static function create()
		{
			return new self();
		}

		public function define_columns($context = null)
		{
			$this->define_relation_column('name', 'product_facet', 'Facet Name', db_varchar, '@name')->validation()->fn('trim')->required();
			$this->define_column('value_pickup', 'Value');
			$this->define_relation_column('value', 'facet_value', 'Value', db_varchar, '@name' )->validation()->fn('trim');
		}

		public function define_form_fields($context = null)
		{
			$this->add_form_field('name')->emptyOption('<known marsian values>')->comment('Please select a attribute, or choose an existing value.', 'above')->cssClassName('relative');
			$this->add_form_field('value_pickup')->renderAs(frm_dropdown)->emptyOption('<known marsian values>')->comment('Please enter a value to the text field below, or choose an existing value.', 'above')->cssClassName('relative');
			$this->add_form_field('value')->renderAs(frm_textarea)->size('small')->noLabel()->cssClassName('relative');
		}

		public function get_value_pickup_options($key = -1)
		{
			$result = array();

			$facet_id = mb_strtolower(trim($this->facet_id));
			
			$values = Db_DbHelper::objectArray('select id, name as value from facetednavigation_facet_values where facet_id=? order by front_end_sort_order', $facet_id);
			foreach ($values as $value_obj)
			{
				$value = Phpr_Html::strTrim(str_replace("\n", " ", $value_obj->value), 40);
				$result[$value_obj->id] = $value;
			}

			return $result;
		}

		public function get_facet_values($facet_id)
		{
			if (!strlen($facet_id))
				return;
				
			$this->value_pickup = Db_DbHelper::scalar('select name from facetednavigation_facet_values where facet_id=?', $facet_id);
		}

		public function load_value($id)
		{
			if (!strlen($id))
				return;
				
			$this->value = Db_DbHelper::scalar('select name from facetednavigation_facet_values where id=?', $id);
		}
	}

?>