<?php
/*
 * This file is part of the pixSortableBehaviorBundle.
 *
 * (c) Nicolas Ricci <nicolas.ricci@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pix\SortableBehaviorBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Pix\SortableBehaviorBundle\Services\PositionHandler;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class SortableAdminController
 *
 * @package Pix\SortableBehaviorBundle
 */
class SortableAdminController extends CRUDController
{
    public function __construct(PositionHandler $positionHandler)
    {
        $this->positionHandler = $positionHandler
    }
    /**
     * Move element
     *
     * @param string $position
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function moveAction($position)
    {
        $translator = $this->get('translator');

        if (!$this->admin->isGranted('EDIT')) {
            $this->addFlash(
                'sonata_flash_error',
                $translator->trans('flash_error_no_rights_update_position')
            );

            return new RedirectResponse($this->admin->generateUrl(
                'list',
                array('filter' => $this->admin->getFilterParameters())
            ));
        }

        /** @var PositionHandler $positionHandler */
        //$positionHandler = $this->get('pix_sortable_behavior.position');
        $object          = $this->admin->getSubject();

        $lastPositionNumber = $this->positionHandler->getLastPosition($object);
        $newPositionNumber  = $this->positionHandler->getPosition($object, $position, $lastPositionNumber);

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($object, $this->positionHandler->getPositionFieldByEntity($object), $newPositionNumber);

        $this->admin->update($object);

        if ($this->isXmlHttpRequest()) {
            return $this->renderJson(array(
                'result' => 'ok',
                'objectId' => $this->admin->getNormalizedIdentifier($object)
            ));
        }

        $this->addFlash(
            'sonata_flash_success',
            $translator->trans('flash_success_position_updated')
        );

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            array('filter' => $this->admin->getFilterParameters())
        ));
    }
}
