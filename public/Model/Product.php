<?php


class Model_Product extends Model
{

	// Main database table for this model
	protected $__table = '_products';

	/**
	 * attempt (default)
	 *    Attempts to remove entry
	 *    Fails silently (returns an Answer object with all relevant info)
	 *
	 * delete
	 *    Attempts to remove entry, on failure set @delete_flag_field to 1
	 *    Throws Exception if @delete_flag_field is not defined
	 *    Throws Exception if it cannot delete nor flag the entry/entries
	 *
	 * force
	 *    Attempts to remove entry, agressively resolve constraints (TODO)
	 *    Throws Exception if it cannot resolve/cancel/remove all constraints
	 *
	 * disable
	 *    Sets @delete_flag_field := 1
	 *    Throws Exception if @delete_flag_field is not defined
	 *    Throws Exception if it cannot update @delete_flag_field
	 *
	 * Delete and disable throw an Exception an if @delete_flag_field is not set
	 * All fail silently if constraints prevent deletion (returns Answer), but
	 *
	 */
	protected $delete_strategy = 'delete';
	protected $delete_flag_field = 'disabled';

}