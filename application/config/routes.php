<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['headline/create']            = "form/index/add/headline";
$route['cluster/create']             = "form/index/add/cluster";
$route['cluster/create/(:any)']      = "form/index/add/cluster/$1";
$route['article/create']             = "form/index/add/article";
$route['article/create/(:any)']      = "form/index/add/article/$1";

$route['headline/modify/(:any)']     = "form/index/edit/headline/$1";
$route['cluster/modify/(:any)']      = "form/index/edit/cluster/$1";
$route['article/modify/(:any)']      = "form/index/edit/article/$1";

$route['headline/destroy/(:any)']    = "actions/index/delete/headline/$1";
$route['cluster/destroy/(:any)']     = "actions/index/delete/cluster/$1";
$route['article/destroy/(:any)']     = "actions/index/delete/article/$1";

$route['headline/unlink/(:any)']     = "actions/index/remove/headline/$1";
$route['cluster/unlink/(:any)']      = "actions/index/remove/cluster/$1";
$route['article/unlink/(:any)']      = "actions/index/remove/article/$1";

$route['headline/(:any)']            = "item/index/headline/$1";
$route['cluster/(:any)']             = "item/index/cluster/$1";
$route['article/(:any)']             = "item/index/article/$1";

$route['h/create']                   = "form/hashed/add/headline";
$route['c/create']                   = "form/hashed/add/cluster";
$route['c/create/(:any)']            = "form/hashed/add/cluster/$1";
$route['a/create']                   = "form/hashed/add/article";
$route['a/create/(:any)']            = "form/hashed/add/article/$1";

$route['h/modify/(:any)']            = "form/hashed/edit/headline/$1";
$route['c/modify/(:any)']            = "form/hashed/edit/cluster/$1";
$route['a/modify/(:any)']            = "form/hashed/edit/article/$1";

$route['h/destroy/(:any)']           = "actions/hashed/delete/headline/$1";
$route['c/destroy/(:any)']           = "actions/hashed/delete/cluster/$1";
$route['a/destroy/(:any)']           = "actions/hashed/delete/article/$1";

$route['h/unlink/(:any)']            = "actions/hashed/remove/headline/$1";
$route['c/unlink/(:any)']            = "actions/hashed/remove/cluster/$1";
$route['a/unlink/(:any)']            = "actions/hashed/remove/article/$1";

$route['h/(:any)']                   = "item/hashed/headline/$1";
$route['c/(:any)']                   = "item/hashed/cluster/$1";
$route['a/(:any)']                   = "item/hashed/article/$1";

$route['search']                     = "lists/index";

$route['user/(:any)']                = "user/index/$1";

$route['u/(:any)']                   = "user/index/$1";

$route['page/(:any)']                = "page/index/$1";

$route['p/(:any)']                   = "page/index/$1";

$route['admin']                      = "admin/users";

$route['default_controller']         = "login";
$route['404_override']               = 'page/index/404';


/* End of file routes.php */
/* Location: ./application/config/routes.php */