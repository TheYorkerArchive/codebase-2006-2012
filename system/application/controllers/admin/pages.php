<?php

/// Pages admin controller.
/**
 * @author James Hogan (jh559@cs.york.ac.uk)
 *
 * This will allow management of the following database tables:
 *	- pages
 *	- page_properties
 *	- property_types
 *
 * And will have special editors for special property types:
 *	- plain text
 *	- wikitext (option to update or clear the cache)
 *	- images + photos
 */
class Pages extends Controller
{
	/// array Map of permissions to role permissions.
	protected $mPermissionMap;

	/// array Permissions array of bools indexed by permission name.
	protected $mPermissions;
	
	function __construct()
	{
		parent::Controller();
	}

    /// Map in a new permission.
    private function _NewPermission($permissions, $rolePermission = null)
    {
        if ( !isset($this->mPermissionMap) )
        {
            $this->mPermissionMap = array();
            $this->mPermissions = array();
            $this->load->model('permissions_model');
        }
        if ( !is_array($permissions) )
        {
            $permissions = array($permissions => $rolePermission);
        }
        foreach ($permissions as $name => $permission)
        {
            $this->mPermissionMap[$name] = $permission;
            $this->mPermissions[$name] = $this->permissions_model->hasUserPermission($permission);
        }
    }

	/// Cardcoded permission
	private function _HardPermission($permissions, $enabled = null)
	{
		if ( !isset($this->mPermissionMap) )
		{
			$this->mPermissionMap = array();
			$this->mPermissions = array();
			$this->load->model('permissions_model');
		}
		if ( !is_array($permissions) )
		{
			$permissions = array($permissions => $enabled);
		}
		foreach ($permissions as $name => $value)
		{
			$this->mPermissionMap[$name] = '';
			$this->mPermissions[$name] = $value;
		}
	}
	
	/// Initialise permissions arrays
	private function _InitPermissions()
	{
		// Construct a mapping of permissions to role permissions
		$this->_NewPermission(array(
			'view'          => 'PAGES_VIEW',
			'common_add'    => 'PAGES_COMMON_ADD',
			'common_edit'   => 'PAGES_COMMON_MODIFY',
			'common_delete' => 'PAGES_COMMON_DELETE',
		));
		foreach (array(	'page'     => 'PAGE',
						'custom'   => 'CUSTOM'	)
					as	$pageType  => $pageTypePermission)
		{
			foreach(array(	'new'          => 'NEW',
							'edit'         => 'MODIFY',
							'rename'       => 'RENAME',
							'delete'       => 'DELETE',
							'prop_add'     => 'PROPERTY_ADD',
							'prop_edit'    => 'PROPERTY_MODIFY',
							'prop_delete'  => 'PROPERTY_DELETE'	)
						as	$propName      => $propNamePermission)
			{
				$this->_NewPermission($pageType.'_'.$propName, 'PAGES_'.$pageTypePermission.'_'.$propNamePermission);
			}
		}
	}

	/// Remap a permission.
	/**
	 * Create the permission @p $name as an alias of the permission @p $mapTo.
	 */
	private function _RemapPermission($remap, $mapTo = null)
	{
		if ( !isset($this->mPermissionMap) )
		{
			$this->_InitPermissions();
		}
		if ( !is_array($remap) )
		{
			$remap = array($remap => $mapTo);
		}
		foreach ($remap as $alias => $name)
		{
			$this->mPermissionMap[$alias] = $this->mPermissionMap[$name];
			$this->mPermissions[$alias] = $this->mPermissions[$name];
		}
	}
	
	// Exit if the user doesn't have the specified permission.
	private function _ExitWithoutPermission($name)
	{
		if ( !isset($this->mPermissionMap) )
		{
			$this->_InitPermissions();
		}
		if (!CheckRolePermissions($this->mPermissionMap[$name]))
		{
			exit(0);
		}
	}
	// Passively check the permission.
	private function _CheckPermission($name)
	{
		if ( !isset($this->mPermissionMap) )
		{
			$this->_InitPermissions();
		}
		return CheckRolePermissions(true, $this->mPermissionMap[$name]);
	}
	
	/// Default page
	function index()
	{
		if (!CheckPermissions('editor')) return;
		$this->_ExitWithoutPermission('view');

		$this->pages_model->SetPageCode('admin_pages');
	
		$all_pages = $this->pages_model->GetAllPages();
			
		$data = array();
			
		$data['pages'] = array(0 => FALSE);
		$data['custom'] = array();
			
		foreach ($all_pages as $key => $page) {
			if ('custom:' == substr($page['page_codename'],0,7)) {
				$page['codename'] = substr($page['page_codename'],7);
				$data['custom'][] = $page;
			} elseif ($page['page_id'] != 0) {
				$page['codename'] = $page['page_codename'];
				$data['pages'][] = $page;
			} else {
				$page['codename'] = $page['page_codename'];
				$data['pages'][0] = $page;
			}
		}
		if (FALSE === $data['pages'][0]) {
			unset($data['pages'][0]);
		}
			
		$main_text = $this->pages_model->GetPropertyWikitext('main_text');
		$data['main_text'] = $main_text;
		$inline_edit_text = $this->pages_model->GetPropertyWikitext('inline_edit_text');
		$data['inline_edit_text'] = $inline_edit_text;
		
		$data['permissions'] = $this->mPermissions;
		$this->main_frame->SetContentSimple('admin/pages_index', $data);
		$this->main_frame->Load();
	}
	
	/// Get page times including dummy "normal" page.
	/**
	 */
	private function _GetPageTypes()
	{
		$result = array(-1 => array(
			'name'         => 'Normal',
			'http_header'  => NULL
		));
		foreach ($this->pages_model->GetAllPageTypes() as $k => $v) {
			$result[$k] = $v;
		}
		return $result;
	}
	
	/// Validates a page codename.
	/**
	 * @param $Codename string The new codename.
	 * @param $Prefix   string The prefix for the codename.
	 * @return bool Whether the codename is valid and unused.
	 */
	private function _ValidatePageCodename($Codename, $Prefix = '')
	{
		// Validate codename, must be non empty, and consist only of certain characters
		if (!preg_match('/^[\w:]+$/', $Codename)) {
			$this->messages->AddMessage('error','Your specified codename "'.xml_escape($Codename).'" contained invalid characters. Please use alphanumeric characters only.');
			return FALSE;
		}
		// Check if new codename is in use
		elseif (FALSE !== $this->pages_model->PageCodeInUse($Prefix.$Codename)) {
			$this->messages->AddMessage('error','A page with the codename "'.xml_escape($Codename).'" already exists. Please choose another.');
			return FALSE;
		}
		return TRUE;
	}
	
	/// Setup the data array for the view for creating a new page.
	/**
	 * @param $Data array Data used for setting up the page.
	 * @param $Target string Page to send the updated data to.
	 * @param $Redirect string Page to direct to after successful save.
	 * @param $Prefix string Prefix to add to codenames.
	 * @return array Modified @a $Data array ready for view.
	 * @pre The user has permission to do so.
	 */
	private function _NewPage($Data, $Target, $Redirect, $Prefix = '')
	{
		$Data['permissions']['prop_add'] = FALSE;
		$Data['permissions']['rename']   = TRUE;
		
		$Data['show_details']   = TRUE;
		
		$Data['target'] = $Target;
		$Data['codename'] = '';
		$Data['head_title'] = '';
		$Data['body_title'] = '';
		$Data['title_separate'] = FALSE;
		$Data['description'] = '';
		$Data['keywords'] = '';
		$Data['type_id'] = -1;
		$Data['properties'] = array();
		$Data['page_types'] = $this->_GetPageTypes();
		
		$input['codename']    = $this->input->post('codename');
		if (FALSE !== $input['codename']) {
			$input['head_title']  = $this->input->post('head_title');
			$input['title_separate']  = ($this->input->post('title_separate') !== FALSE);
			if (!$input['title_separate']) {
				$input['body_title'] = $this->input->post('head_title');
			} else {
				$input['body_title'] = $this->input->post('body_title');
			}
			$input['description'] = $this->input->post('description');
			$input['keywords']    = $this->input->post('keywords');
			$input['type_id']     = $this->input->post('type_id');
			$save_failed = FALSE;
		
			// Validate and check permission
			if ($this->_CheckPermission('new')) {
				$Data['codename'] = $input['codename'];
				// Check the codename is valid
				if (!$this->_ValidatePageCodename($input['codename'], $Prefix)) {
					$save_failed = TRUE;
				}
			} else {
				$save_failed = TRUE;
			}
			if (FALSE !== $input['head_title'])
				$Data['head_title'] = $input['head_title'];
			if (FALSE !== $input['body_title'])
				$Data['body_title'] = $input['body_title'];
			$Data['title_separate'] = $input['title_separate'];
			if (FALSE !== $input['description'])
				$Data['description'] = $input['description'];
			if (FALSE !== $input['keywords'])
				$Data['keywords'] = $input['keywords'];
			if (FALSE !== $input['type_id']) {
				if (!is_numeric($input['type_id'])
						|| !array_key_exists((int)$input['type_id'], $Data['page_types'])) {
					$this->messages->AddMessage('warning','The specified page type does not exist the new page will be normal.');
					unset($input['type_id']);
				} else {
					$Data['type_id'] = $input['type_id'] = (int)$input['type_id'];
				}
			}
				
			// If don't separate titles, ignore body title
			if (!$input['title_separate']) {
				$input['body_title'] = NULL;
			}
			
			if (FALSE === $save_failed) {
				// Try and save to db
				$input['codename'] = $Prefix.$input['codename'];
				if ($this->pages_model->CreatePage($input)) {
					$this->messages->AddMessage('success', 'The page was successfully saved');
					redirect($Redirect.$Data['codename']);
				} else {
					$this->messages->AddMessage('error', 'The page could not be saved as an internal error occurred');
					$save_failed = TRUE;
				}
			}
		}
		
		$main_text = $this->pages_model->GetPropertyWikitext('main_text');
		$Data['main_text'] = $main_text;
		return $Data;
	}
	
	/// Setup the data array for the view for editing a page.
	/**
	 * @param $Data array Data used for setting up the page.
	 * @param $InputPageCode string/FALSE Page code from uri or FALSE for common.
	 * @param $Target string Page to send the updated data to.
	 * @param $Redirect string Page to direct to after successful save.
	 * @param $DefaultProperties array Set of properties to create automatically
	 *	if they don't exist (and to display first). Must have:
	 *	- 'label'
	 *	- 'type'
	 *	- 'text'
	 * @param $Prefix string Prefix to add to codenames.
	 * @return array Modified @a $Data array ready for view.
	 */
	private function _EditPage(
			$Data,
			$InputPageCode,
			$Target,
			$Redirect,
			$DefaultProperties = array(),
			$Prefix = '')
	{
		$data = $Data;
		$global_scope = (FALSE === $InputPageCode);
		if (!$global_scope)
			$page_code = $Prefix.$InputPageCode;
		else
			$page_code = $InputPageCode;
		// Find the custom page code
		$page_info = $this->pages_model->GetSpecificPage($page_code, TRUE);
		if (FALSE === $page_info) {
			$this->messages->AddMessage('error', 'The page named "'.$page_code.'" doesn\'t exist');
			redirect('admin/pages');
		} else {
			$data['show_details'] = !$global_scope;
			if (!$global_scope) {
				$data['target']      = $Target.$InputPageCode;
				$data['codename']    = $InputPageCode;
				$data['head_title']  = $page_info['head_title'];
				$data['title_separate'] = (NULL !== $page_info['body_title']);
				if (!$data['title_separate']) {
					$data['body_title'] = $page_info['head_title'];
				} else {
					$data['body_title'] = $page_info['body_title'];
				}
				$data['description'] = $page_info['description'];
				$data['keywords']    = $page_info['keywords'];
				$data['type_id']     = (NULL !== $page_info['type_id'] ? (int)$page_info['type_id'] : -1);
				$data['properties']  = array();
				$data['page_types']  = $this->_GetPageTypes();
			} else {
				$data['target']      = $Target;
			}
			
			// DefaultProperties has default properties (without id's)
			// page_info['properties'] has current properties (with id's)
			$data['properties']  = array();
			$new_id = 0;
			$properties = array();
			foreach ($page_info['properties'] as $property) {
				if ($property['type'] !== 'wikitext_cache') {
					$key = $property['label'].'|'.$property['type'];
					$properties[$key] = array(
							'status'=> 'prop',
							'id'    => $property['id'],
							'label' => $property['label'],
							'type'  => $property['type'],
							'text'  => $property['text'],
						);
				}
			}
			foreach ($DefaultProperties as $property) {
				$key = $property['label'].'|'.$property['type'];
				if (!array_key_exists($key, $properties)) {
					$properties[$key] = array(
							'status'=> 'newprop',
							'id'    => (--$new_id),
							'label' => $property['label'],
							'type'  => $property['type'],
							'text'  => $property['text'],
						);
				}
			}
			$data['properties'] = array();
			foreach ($properties as $property) {
				$data['properties'][$property['id']] = array(
						'id'    => $property['status'].$property['id'],
						'label' => $property['label'],
						'type'  => $property['type'],
						'text'  => $property['text'],
					);
			}
			
			if (!$global_scope) {
				$input['codename']    = $this->input->post('codename');
				if (FALSE !== $input['codename']) {
					$input['head_title']  = $this->input->post('head_title');
					$input['body_title']  = $this->input->post('body_title');
					$input['title_separate']  = ($this->input->post('title_separate') !== FALSE);
					if (!$input['title_separate']) {
						$input['body_title'] = $this->input->post('head_title');
					} else {
						$input['body_title'] = $this->input->post('body_title');
					}
					$input['description'] = $this->input->post('description');
					$input['keywords']    = $this->input->post('keywords');
					$input['type_id']     = $this->input->post('type_id');
					$save_failed = FALSE;
					
					// Validate and check permissions
					if (!$this->_CheckPermission('edit')) {
						$save_failed = TRUE;
					}
					elseif ($input['codename'] != $data['codename']) {
						if ($this->_CheckPermission('rename')) {
							$data['codename'] = $input['codename'];
							// Check the codename is valid
							if (!$this->_ValidatePageCodename($input['codename'], $Prefix)) {
								$save_failed = TRUE;
							}
						} else {
							$save_failed = TRUE;
						}
					}
					if (FALSE !== $input['head_title'])
						$data['head_title'] = $input['head_title'];
					if (FALSE !== $input['body_title'])
						$data['body_title'] = $input['body_title'];
					$data['title_separate'] = $input['title_separate'];
					if (FALSE !== $input['description'])
						$data['description'] = $input['description'];
					if (FALSE !== $input['keywords'])
						$data['keywords'] = $input['keywords'];
					if (FALSE !== $input['type_id']) {
						if (!is_numeric($input['type_id'])
								|| !array_key_exists((int)$input['type_id'], $data['page_types'])) {
							$this->messages->AddMessage('warning','The specified page type does not exist and will not be changed.');
							unset($input['type_id']);
						} else {
							$data['type_id'] = $input['type_id'] = (int)$input['type_id'];
						}
					}
				
					// If don't separate titles, ignore body title
					if (!$input['title_separate']) {
						$input['body_title'] = NULL;
					}
					
					if (FALSE === $save_failed) {
						// Try and save to db
						$input['codename'] = $Prefix.$input['codename'];
						if ($this->pages_model->SaveSpecificPage($page_code, $input)) {
							$this->messages->AddMessage('success', 'The page was successfully saved');
							if ($data['codename'] != $page_code) {
								redirect($Redirect.$data['codename']);
							}
						} else {
							$this->messages->AddMessage('error','The page could not be saved as an internal error occurred');
						}
					}
				}
			}
			if ($this->input->post('save_properties') !== FALSE) {
				$changes = 0;
				$input = array();
				$input['properties'] = array();
				$input['property_add'] = array();
				$input['property_remove'] = array('wikitext_cache' => array());
				$ignored_new_props = 0;
				$newProperties     = 0;
				$changedProperties = 0;
				$deletedProperties = 0;
				foreach ($_POST as $key => $value) {
					if (preg_match('/^prop(\d+)$/',$key,$matches)) {
						$property_id = (int)$matches[1];
						if (array_key_exists($property_id, $data['properties'])) {
							if (array_key_exists('delete-'.$key, $_POST)) {
								// Property needs deleting
								$input['property_remove'][$data['properties'][$property_id]['type']][] = $data['properties'][$property_id]['label'];
								if ($data['properties'][$property_id]['type'] === 'wikitext') {
									$input['property_remove']['wikitext_cache'][] = $data['properties'][$property_id]['label'];
									++$changes;
								}
								++$changes;
								++$deletedProperties;
							} elseif ($data['properties'][$property_id]['text'] != $value) {
								// property has been changed
								$input['properties'][] = array(
										'id' => $property_id,
										'text' => $value,
									);
								++$changes;
								++$changedProperties;
								$data['properties'][$property_id]['text'] = $value;
								if ($data['properties'][$property_id]['type'] === 'wikitext') {
									$input['property_remove']['wikitext_cache'][] = $data['properties'][$property_id]['label'];
									++$changes;
								}
							}
						}
					}
					if (preg_match('/^newprop(\-?\d+)$/',$key,$matches)) {
						$label_key = 'label-'.$key;
						$type_key = 'type-' .$key;
						if (	array_key_exists($label_key, $_POST) &&
								array_key_exists($type_key, $_POST)) {
							$label = $_POST[$label_key];
							$type  = $_POST[$type_key];
							if (empty($label) && empty($value)) {
								++$ignored_new_props;
							} else {
								// New property
								$input['property_add'][] = array(
										'label'	=> $label,
										'type'	=> $type,
										'text'	=> $value,
									);
								++$changes;
								++$newProperties;
							}
						}
					}
				}

				// Check permissions
				if ((0 == $newProperties     || $this->_CheckPermission('prop_add')) &&
					(0 == $deletedProperties || $this->_CheckPermission('prop_delete')) &&
					(0 == $changedProperties || $this->_CheckPermission('prop_edit')))
				{
					if ($ignored_new_props == 1) {
						$this->messages->AddMessage('information',$ignored_new_props.' new property was ignored as it was blank');
					} elseif ($ignored_new_props > 1) {
						$this->messages->AddMessage('information',$ignored_new_props.' new properties were ignored as they were blank');
					}
				
					if ($changes > 0) {
						// Try and save to db
						if ($this->pages_model->SaveSpecificPage($page_code, $input)) {
							$this->messages->AddMessage('success', 'Properties were successfully saved.');
						} else {
							$this->messages->AddMessage('error','Properties weren\'t save. An internal error occurred.');
						}
					} else {
						$this->messages->AddMessage('information', 'No properties have changed');
					}
				}
				$_POST = array();
				return $this->_EditPage($Data, $InputPageCode, $Target, $Redirect, $DefaultProperties, $Prefix);
			}
		}
		$main_text = $this->pages_model->GetPropertyWikitext('main_text');
		$data['main_text'] = $main_text;
		$page_help = $this->pages_model->GetPropertyWikitext('special:help',$page_code);
		$data['page_help'] = $page_help;
		return $data;
	}
	
	/// Delete a page.
	/**
	 * @pre The user has permission to do so.
	 */
	private function _DeletePage(
			$Data,
			$InputPageCode,
			$Target,
			$Prefix = '')
	{
		$data = $Data;
		$data['target'] = $Target.$InputPageCode;
		if (FALSE === $this->input->post('confirm_delete')) {
			// Get information about the page so user is informed before confirming.
			$information = $this->pages_model->GetSpecificPage($Prefix.$InputPageCode, TRUE);
			if (FALSE === $information) {
				$this->messages->AddMessage('error','Page \'' . $InputPageCode . '\' not found');
				$data['confirm'] = FALSE;
			} else {
				$data['confirm'] = TRUE;
			}
			$data['information'] = $information;
			
		} else {
			$data['confirm'] = FALSE;
			// user confirmed, delete the page and its properties.
			$result = $this->pages_model->DeletePage($Prefix.$InputPageCode);
			if ($result) {
				// Success
				$this->messages->AddMessage('success','The page was successfully deleted.');
				redirect('admin/pages');
			} else {
				// Failure
				$this->messages->AddMessage('error','The page could not be deleted.');
				redirect('admin/pages');
			}
		}
		$main_text = $this->pages_model->GetPropertyWikitext('main_text');
		$data['main_text'] = $main_text;
		return $data;
	}
	
	/// Enable / disable inline edit mode.
	function inline($change = NULL)
	{
		$valid_changes = array('on' => true, 'off' => false);
		if (!isset($valid_changes[$change])) {
			show_404();
		}
		if (!CheckPermissions('office')) return;
		
		// Get redirection tail
		$args = func_get_args();
		array_shift($args);
		$tail = implode('/', $args);
		
		// Make the change
		$change_to = $valid_changes[$change];
		$new_inline = $this->pages_model->SetInlineEditMode($change_to);
		if ($new_inline) {
			$this->messages->AddMessage('success', 'Inline edit mode enabled');
		} elseif ($change_to) {
			$this->messages->AddMessage('error',   'Inline edit mode could not be enabled');
		} else {
			$this->messages->AddMessage('success', 'Inline edit mode disabled');
		}
		
		// Redirect
		redirect($tail);
	}
	
	/// Function for administrating common properties (global scope)
	/**
	 */
	function common()
	{
		if (!CheckPermissions('editor')) return;
		$this->_ExitWithoutPermission('view');

		// Tweak the permissions
		$this->_HardPermission(array(
			'new'    => false,
			'edit'   => false,
			'rename' => false,
			'delete' => false,
		));
		$this->_RemapPermission(array(
			'prop_add'    => 'common_add',
			'prop_edit'   => 'common_edit',
			'prop_delete' => 'common_delete',
		));
		
		$this->main_frame->IncludeJs('javascript/admin/pages.js');
		$data = array();
		$data['permissions'] = $this->mPermissions;
			
		$this->pages_model->SetPageCode('admin_pages_common');
		$this_uri = '/admin/pages/common';
		$data = $this->_EditPage($data, FALSE, $this_uri, $this_uri);
		$this->main_frame->SetContentSimple('admin/pages_page', $data);
		$this->main_frame->Load();
	}
	
	/// Function for administrating pages of the website.
	/**
	 * @param $Operation string An operation to perform:
	 *	- new
	 *	- edit (@a $PageCode required)
	 * @param $PageCode string Page code of page to edit.
	 */
	function page($Operation, $PageCode='')
	{
		if (!CheckPermissions('office')) return;
		$this->_ExitWithoutPermission('view');
		
		// Tweak the permissions
		$this->_RemapPermission(array(
			'new'         => 'page_new',
			'edit'        => 'page_edit',
			'rename'      => 'page_rename',
			'delete'      => 'page_delete',
			'prop_add'    => 'page_prop_add',
			'prop_edit'   => 'page_prop_edit',
			'prop_delete' => 'page_prop_delete',
		));
		
		// Get url segments after the first (controller).
		$num_segments = $this->uri->total_segments();
		$segments = array();
		for ($counter = 5; $counter <= $num_segments; ++$counter) {
			$segments[] = $this->uri->segment($counter);
		}
		$PageCode = implode('/',$segments);
		// We now have the page code so we can continue.
		
		$this->main_frame->IncludeJs('javascript/admin/pages.js');
		$data = array();
		$data['permissions'] = $this->mPermissions;
		switch ($Operation) {
			case 'new':
				$this->_ExitWithoutPermission('new');
				$this->pages_model->SetPageCode('admin_pages_page_new');
				$data = $this->_NewPage($data,
								'/admin/pages/page/new',
								'/admin/pages/page/edit/');
				$this->main_frame->SetContentSimple('admin/pages_page', $data);
				break;
					
			case 'edit':
				$this->pages_model->SetPageCode('admin_pages_page_edit');
				$this_uri = '/admin/pages/page/edit/';
				$data = $this->_EditPage($data, $PageCode, $this_uri, $this_uri);
				$this->main_frame->SetContentSimple('admin/pages_page', $data);
				break;
					
			case 'delete':
				$this->_ExitWithoutPermission('delete');
				$this->pages_model->SetPageCode('admin_pages_page_delete');
				$this_uri = '/admin/pages/page/delete/';
				$data = $this->_DeletePage($data, $PageCode, $this_uri);
				$this->main_frame->SetContentSimple('admin/pages_delete', $data);
				break;
					
			default:
				$this->messages->AddMessage('error', 'Unknown operation "'.$Operation.'"');
				redirect('admin/pages');
				return;
		}
		$this->main_frame->SetTitleParameters( array(
				'codename' => $PageCode,
			) );
		$this->main_frame->Load();
	}
	
	/// Function for administrating custom pages.
	/**
	 * @param $Operation string An operation to perform:
	 *	- new
	 *	- edit (@a $PageCode required)
	 * @param $PageCode string Page code of custom page to edit
	 *	(without preceeding 'custom:').
	 */
	function custom($Operation, $CustomPageCode='')
	{
		if (!CheckPermissions('office')) return;
        $this->_ExitWithoutPermission('view');

        // Tweak the permissions
        $this->_RemapPermission(array(
            'new'         => 'custom_new',
            'edit'        => 'custom_edit',
            'rename'      => 'custom_rename',
            'delete'      => 'custom_delete',
            'prop_add'    => 'custom_prop_add',
            'prop_edit'   => 'custom_prop_edit',
            'prop_delete' => 'custom_prop_delete',
        ));
		
		// Get url segments after the first (controller).
		$num_segments = $this->uri->total_segments();
		$segments = array();
		for ($counter = 5; $counter <= $num_segments; ++$counter) {
			$segments[] = $this->uri->segment($counter);
		}
		$CustomPageCode = implode('/',$segments);
		// We now have the page code so we can continue.
		
		$this->main_frame->IncludeJs('javascript/admin/pages.js');
		$data = array();
		$data['permissions'] = $this->mPermissions;
		switch ($Operation) {
			case 'new':
				$this->_ExitWithoutPermission('new');
				$this->pages_model->SetPageCode('admin_pages_custom_new');
				$data = $this->_NewPage($data,
								'/admin/pages/custom/new',
								'/admin/pages/custom/edit/',
								'custom:');
				$this->main_frame->SetContentSimple('admin/pages_page', $data);
				break;
				
			case 'edit':
				$this->pages_model->SetPageCode('admin_pages_custom_edit');
				$this_uri = '/admin/pages/custom/edit/';
				$data = $this->_EditPage($data, $CustomPageCode, $this_uri, $this_uri,
						array(
							array(
								'label' => 'main[0]',
								'type' => 'wikitext',
								'text' => 'Your page content goes here.',
							),
						),
						'custom:'
					);
				$this->main_frame->SetContentSimple('admin/pages_page', $data);
				break;
					
			case 'delete':
				$this->_ExitWithoutPermission('delete');
				$this->pages_model->SetPageCode('admin_pages_custom_delete');
				$this_uri = '/admin/pages/custom/delete/';
				$data = $this->_DeletePage($data, $CustomPageCode, $this_uri, 'custom:');
				$this->main_frame->SetContentSimple('admin/pages_delete', $data);
				break;
					
			default:
				$this->messages->AddMessage('error', 'Unknown operation "'.$Operation.'"');
				redirect('admin/pages');
				return;
		}
		$this->main_frame->SetTitleParameters( array(
				'codename' => $CustomPageCode,
			) );
		$this->main_frame->Load();
	}
	
	/// Post->XML function to make inline page property edits.
	function inlineedit()
	{
		$this->load->model('user_auth');
		$this->load->model('pages_model');
		$data = array();
		if (!$this->pages_model->GetInlineEditMode()) {
			// Not enough permissions
			$data['Fail'] = true;
			$data['Saved'] = false;
			$data['Preview'] = NULL;
		} else {
			$input_data = array();
			foreach (array('action','pageid','property','type','text') as $field) {
				if (isset($_GET[$field])) {
					$input_data[$field] = $_GET[$field];
				} else {
					// Don't bother outputting anything, insufficient data.
					return;
				}
			}
			if ($input_data['pageid'] == '_common') {
				$input_data['pageid'] = TRUE;
			}
			
			$data['Fail'] = false;
			$data['Saved'] = false;
			$data['Preview'] = NULL;
			switch ($input_data['action']) {
				case 'preview':
				case 'save':
					if ($input_data['type'] == 'wikitext') {
						$this->load->library('wikiparser');
						$data['Preview'] = $this->wikiparser->parse($input_data['text']);
					}
					if ($input_data['action'] == 'save') {
						if ($this->pages_model->InsertProperty($input_data['pageid'], $input_data['property'], $input_data['type'],
								array('text' => $input_data['text'])
							))
						{
							$data['Saved'] = true;
						}
						if ($input_data['type'] == 'wikitext') {
							if ($this->pages_model->InsertProperty($input_data['pageid'], $input_data['property'], 'wikitext_cache',
								array('text' => $data['Preview'])
								))
							{
								$data['Saved'] = true;
							}
						}
					}
					break;
				default:
					// Don't bother outputting anything, invalid action.
					return;
			};
		}
		$this->load->view('admin/pages_inlineedit_xml', $data);
	}
	
}

?>
