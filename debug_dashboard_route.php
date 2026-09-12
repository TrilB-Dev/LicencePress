<?php
require __DIR__ . '/vendor/autoload.php';

$_GET['group'] = 'licences';
$_GET['tab'] = 'manage-types';

$admin = (new ReflectionClass('LicencePress\\Admin\\Admin'))->newInstanceWithoutConstructor();
$manager = new LicencePress\Admin\Manager\Licences\LicencesManager();
$property = new ReflectionProperty('LicencePress\\Admin\\Admin', 'licences_manager');
$property->setAccessible(true);
$property->setValue($admin, $manager);

ob_start();
$admin->render_dashboard();
$output = ob_get_clean();
$group = LicencePress\Includes\Functions\Helpers\RequestHelper::get_key('group', '');
$tab = LicencePress\Includes\Functions\Helpers\RequestHelper::get_key('tab', '');
var_dump($group, $tab);
var_dump(strpos($output, 'Manage Licence Types'));
echo "---OUTPUT---\n";
echo substr($output, 0, 1500);
