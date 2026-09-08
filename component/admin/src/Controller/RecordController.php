<?php
namespace Xdecaro\Component\Decaroprotocol\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Xdecaro\Component\Decaroprotocol\Administrator\Service\ProtocolService;

class RecordController extends FormController
{
    protected $view_list = 'records';

    public function protocol()
    {
        if (!Session::checkToken()) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'));
        }

        $user = $this->app->getIdentity();

        if (!$user->authorise('protocol.protocol', 'com_decaroprotocol')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $data = $this->input->post->get('jform', array(), 'array');
        $model = $this->getModel('Record');

        if (!$model->save($data)) {
            $this->setRedirect(
                Route::_('index.php?option=com_decaroprotocol&view=record&layout=edit&id=' . (int) ($data['id'] ?? 0), false),
                $model->getError(),
                'error'
            );
            return false;
        }

        $id = (int) ($data['id'] ?? 0);
        if ($id < 1) {
            $id = (int) $model->getState('record.id');
        }

        if ($id < 1) {
            $this->setRedirect(Route::_('index.php?option=com_decaroprotocol&view=records', false), Text::_('COM_DECAROPROTOCOL_ERROR_RECORD_ID'), 'error');
            return false;
        }

        try {
            $service = Factory::getContainer()->get(ProtocolService::class);
            $result = $service->protocol($id, (int) $user->id);
            $message = Text::sprintf('COM_DECAROPROTOCOL_PROTOCOL_SUCCESS', $result['number'], $result['year']);
            $this->setRedirect(Route::_('index.php?option=com_decaroprotocol&view=record&layout=edit&id=' . $id, false), $message);
            return true;
        } catch (\Throwable $e) {
            $this->setRedirect(Route::_('index.php?option=com_decaroprotocol&view=record&layout=edit&id=' . $id, false), $e->getMessage(), 'error');
            return false;
        }
    }
}
