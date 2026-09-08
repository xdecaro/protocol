<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

class RegisterController extends FormController
{
    protected $view_list = 'registers';

    protected function allowAdd($data = array())
    {
        return $this->app->getIdentity()->authorise('protocol.manage_registers', 'com_decaroprotocol') && parent::allowAdd($data);
    }

    protected function allowEdit($data = array(), $key = 'id')
    {
        return $this->app->getIdentity()->authorise('protocol.manage_registers', 'com_decaroprotocol') && parent::allowEdit($data, $key);
    }

    public function delete()
    {
        if (!$this->app->getIdentity()->authorise('protocol.manage_registers', 'com_decaroprotocol')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        return parent::delete();
    }
}
