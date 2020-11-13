<?php
/**
 * Checkbox Control modified to include the base.
 * 
 * NOTE: Does NOT support devices.
 * 
 * @see WP_Customize_Color_Control
 */
class Bunyad_Customizer_Controls_Checkbox extends WP_Customize_Control
{
	use Bunyad_Customizer_Controls_BaseTrait;

	public function to_json()
	{
		$this->base_json();
	}
}
