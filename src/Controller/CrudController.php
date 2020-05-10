<?php

namespace Codyas\Toolbox\Controller;

use App\Crud\Model\CrudExportable;
use App\Service\ReportGeneratorService;
use Codyas\Toolbox\Constants;
use Codyas\Toolbox\Events\CrudRecordDeletedEvent;
use Codyas\Toolbox\Model\CrudCancelable;
use Codyas\Toolbox\Model\CrudCustomizable;
use Codyas\Toolbox\Model\CrudOperationable;
use Codyas\Toolbox\Exception\ClientInputException;
use Doctrine\Common\Persistence\ManagerRegistry;
use FOS\UserBundle\Model\UserManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CrudController extends AbstractController
{
	/**
	 * @Route("/crud/fetch/{entity}", name="crud_fetch")
	 */
	public function fetch( ManagerRegistry $registry, $entity, PaginatorInterface $paginator, Request $request, TranslatorInterface $translator )
	{
		$this->isActionAllowed( $entity, 'view' );
		$filterFormType = $entity::getFilterFormType();
		$filter         = [];
		if ( $filterFormType )
		{
			$filterForm = $this->createForm( $filterFormType, [] );
			$filterForm->submit( $request->get( $filterForm->getName() ) );
			$filter = $filterForm->getData();
		}

		$start          = $request->query->get( 'start', 0 );
		$length         = $request->query->get( 'length', 10 );
		$page           = intval( $start / $length ) + 1;
		$em             = $registry->getManager();
		$orderColumn    = $request->query->get( 'order_column', 'id' );
		$orderDirection = $request->query->get( 'order_dir', 'desc' );

		if ( $entity::hasCustomFetchMethod() )
		{
			$filter['order_column']    = $orderColumn;
			$filter['order_direction'] = $orderDirection;
			$query                     = $em->getRepository( $entity )->fetch( $filter );
		} else
		{
			$query = $em
				->getRepository( $entity )
				->createQueryBuilder( 'e' );
			if ( $orderColumn )
			{
				$query->orderBy( 'e.' . $orderColumn, $orderDirection );
			}
			$query->getQuery();
		}
		$pagination = $paginator->paginate( $query, $page, $length );

		$response = [];
		foreach ( $pagination as $key => $item )
		{
			if ( $item instanceof CrudOperationable )
			{
				if ( $item instanceof CrudCustomizable )
				{
					$actionButtons = $item->getActionButtons( $this->get('twig'), $item );
				} else
				{
					$actionButtons = [
						$this->renderView( '@CodyasToolbox/crud/_table_action_buttons.html.twig', [
							'record' => $item,
							'entity' => $entity,
						] )
					];
				}

				$response [] = array_merge(
					$item->showTableIndex() ? [ $key + 1 ] : [],
					$item->renderDataTableRow( [
						'translator' => $translator
					] ),
					$actionButtons
				);
			}
		}

		return $this->json( [
			"data"            => $response,
			"recordsFiltered" => $pagination->getTotalItemCount(),
			"recordsTotal"    => $pagination->getTotalItemCount()
		] );
	}

	private function isActionAllowed( $entity, $action )
	{
		if ( ! $this->isGranted( $entity::getPermission( $action ) ) )
		{
			throw new AccessDeniedHttpException( \App\Constants::ERROR_INVALID_CRUD_CREDENTIALS );
		}
	}

	/**
	 * @Route("/crud/export/{entity}/{format}", name="crud_export")
	 */
	public function export( ReportGeneratorService $reportGeneratorService, $format, $entity, PaginatorInterface $paginator, Request $request, TranslatorInterface $translator, \Symfony\Component\Templating\EngineInterface $twig )
	{
		$start  = $request->query->get( 'start', 0 );
		$length = $request->query->get( 'length', 10 );
		$page   = intval( $start / $length ) + 1;
		$filter = $this->getInputFilter( $entity, $request );

		$pagination = $paginator->paginate( $this->generateFetchQuery( $entity, $request, $filter ), $page, $length );

		if ( ! in_array( CrudExportable::class, class_implements( $entity ) ) )
		{
			throw new RuntimeException( sprintf( 'Entity %s is not exportable.', $entity ) );
		}

		preg_match( '/[\w]+$/', $entity, $fileLabel );
		$exportMethods = $entity::getSupportedExportFormats();
		switch ( $format )
		{
			case 'pdf' and $entity::supportsExportFor( 'pdf' ):
				$exportMethods = $entity::getSupportedExportFormats();
				/** @var TCPDF $pdf */
				$pdf = call_user_func(
					[ $reportGeneratorService, $exportMethods['pdf'] ],
					$pagination
				);

				return new Response( $pdf->Output( sprintf( '%s_%s.pdf', $fileLabel[0], date( 'M_d_Y_Hi' ) ), 'D' ) );
				break;
			case 'xlsx' and $entity::supportsExportFor( 'xlsx' ):
				$group = $this->getDoctrine()->getRepository( Group::class )->find( $filter['group'] );
				if ( ! $group )
				{
					throw $this->createNotFoundException();
				}
				$spreadsheet = new Spreadsheet();
				$spreadsheet = call_user_func(
					[ $reportGeneratorService, $exportMethods['xlsx'] ],
					$pagination, $spreadsheet
				);
				$writer      = new Xlsx( $spreadsheet );
				$fileName    = sprintf( '%s.xlsx', $group->__toString() );
				$tempFile    = tempnam( sys_get_temp_dir(), $fileName );
				$writer->save( $tempFile );

				return $this->file( $tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE );
				break;
		}
	}

	private function getInputFilter( $entity, Request $request )
	{
		$filterFormType = $entity::getFilterFormType();
		$filter         = [];
		if ( $filterFormType )
		{
			$filterForm = $this->createForm( $filterFormType, [] );
			$filterForm->submit( $request->get( $filterForm->getName() ) );
			$filter = $filterForm->getData();
		}

		return $filter;
	}

	private function generateFetchQuery( $entity, Request $request, array $filter )
	{
		$this->isActionAllowed( $entity, 'view' );
		$em = $this->getDoctrine()->getManager();
		if ( $entity::hasCustomFetchMethod() )
		{
			$orderColumn               = $request->query->get( 'order_column', 'id' );
			$orderDirection            = $request->query->get( 'order_dir', 'desc' );
			$filter['order_column']    = $orderColumn;
			$filter['order_direction'] = $orderDirection;
			$query                     = $em->getRepository( $entity )->fetch( $filter );
		} else
		{
			$query = $em->getRepository( $entity )->createQueryBuilder( 'e' )->orderBy( 'e.createdAt', 'DESC' )->getQuery();
		}

		return $query;
	}

	/**
	 * @Route("/new/{entity}", name="crud_new", methods={"GET","POST"})
	 */
	public function newRecord( ManagerRegistry $registry, Request $request, $entity )
	{
		$this->isActionAllowed( $entity, 'create' );
		$instance = new $entity();
		if ( ! $instance instanceof CrudOperationable || ! $instance instanceof CrudCustomizable )
		{
			throw new ClientInputException( Constants::ERROR_INVALID_CRUD_ENTITY );
		}
		$form = $this->buildForm( $instance->getFormType(), $instance, 'new' );
		if ( $request->isMethod( 'POST' ) )
		{
			$form->handleRequest( $request );
			if ( $form->isValid() )
			{
				$em = $registry->getManager();

				if ( array_key_exists( Constants::CREATE, $instance->getPersistenceCallbacks() ) )
				{
					$callbacks     = $instance->getPersistenceCallbacks();
					$eventCallback = $callbacks[ Constants::CREATE ];
					call_user_func( [ $instance, $eventCallback ] );
				}

				$em->persist( $instance );
				$em->flush();

				return $this->json( [], 201 );
			}
		}
		$stCode = $request->isMethod( 'POST' ) ? 400 : 200;

		return $this->render( $instance->getFormTemplate(), [
			'form' => $form->createView()
		], ( new Response( null, $stCode ) ) );
	}

	private function buildForm( $type, CrudOperationable $entity, $intention )
	{
		$routeArgs = [ 'entity' => get_class( $entity ), 'type' => $type ];
		$form      = $this->createForm( $type, $entity, [
			'action' => $intention === 'new'
				? $this->generateUrl( 'crud_new', $routeArgs )
				: $this->generateUrl( 'crud_edit', array_merge( $routeArgs, [ 'id' => $entity->getId() ] ) )
			,
			'attr'   => [
				'method' => 'post',
				'id'     => 'crud-form'
			]
		] );

		return $form;
	}

	/**
	 * @Route("/edit/{entity}/{id}", name="crud_edit", methods={"GET","POST"})
	 */
	public function editRecord( ManagerRegistry $registry, Request $request, $entity, $id, UserManagerInterface $userManager )
	{
		$this->isActionAllowed( $entity, 'edit' );
		$em       = $registry->getManager();
		$instance = $em->getRepository( $entity )->find( $id );
		if ( ! $instance )
		{
			throw $this->createNotFoundException();
		}
		if ( ! $instance instanceof CrudOperationable || ! $instance instanceof CrudCustomizable )
		{
			throw new ClientInputException( Constants::ERROR_INVALID_CRUD_ENTITY );
		}
		$form = $this->buildForm( $instance->getFormType(), $instance, 'edit' );
		if ( $request->isMethod( 'POST' ) )
		{
			$form->handleRequest( $request );
			if ( $form->isValid() )
			{
				if ( $instance instanceof User )
				{
					$userManager->updateUser( $instance );
				}

				if ( array_key_exists( Constants::UPDATE, $instance->getPersistenceCallbacks() ) )
				{
					$callbacks     = $instance->getPersistenceCallbacks();
					$eventCallback = $callbacks[ Constants::UPDATE ];
					call_user_func( [ $instance, $eventCallback ] );
				}

				$em->persist( $instance );
				$em->flush();

				return $this->json( [], 200 );
			}
		}
		$stCode = $request->isMethod( 'POST' ) ? 400 : 200;

		return $this->render( $instance->getFormTemplate(), [
			'form' => $form->createView()
		], ( new Response( null, $stCode ) ) );
	}

	/**
	 * @Route("/remove/{entity}/{id}", name="crud_remove", methods={"DELETE"})
	 */
	public function removeRecord( ManagerRegistry $registry, Request $request, $entity, $id, EventDispatcherInterface $eventDispatcher )
	{
		$this->isActionAllowed( $entity, 'remove' );
		if ( $this->isCsrfTokenValid( Constants::CSRF_VALIDATION_CRUD_REMOVAL, $request->get( 'token' ) ) )
		{
			throw new AccessDeniedHttpException();
		}
		$em = $registry->getManager();
		$instance = $em->getRepository( $entity )->find( $id );
		if ( ! $instance )
		{
			throw $this->createNotFoundException();
		}
		if ( ! $instance instanceof CrudOperationable )
		{
			throw new ClientInputException( Constants::ERROR_INVALID_CRUD_ENTITY );
		}
		if ( $instance instanceof \App\Crud\Model\CrudCancelable )
		{
			$instance->setStatus( Constants::STATUS_CANCELED );
			$em->persist( $instance );
		} else
		{
			if ( $instance instanceof CrudCustomizable )
			{
				$hasRelatedRecords = $instance->hasDependenciesForRemoval();
				if ( $hasRelatedRecords )
				{
					foreach ( $hasRelatedRecords as $errCode )
					{
						throw new ClientInputException( $errCode );
					}
				} else
				{
					$em->remove( $instance );
				}
			} else
			{
				$em->remove( $instance );
			}
		}

		$eventDispatcher->dispatch( new CrudRecordDeletedEvent( $instance ) );
		$em->flush();

		return $this->json( [], 204 );

	}

	/**
	 * @Route("/details/{entity}/{id}", name="crud_details", methods={"GET"})
	 */
	public function recordDetails( ManagerRegistry $registry, Request $request, $entity, $id )
	{
		$this->isActionAllowed( $entity, 'view' );
		$em       = $registry->getManager();
		$instance = $em->getRepository( $entity )->find( $id );
		if ( ! $instance )
		{
			throw $this->createNotFoundException();
		}
		if ( ! $instance instanceof CrudOperationable || ! $instance instanceof CrudCustomizable )
		{
			throw new ClientInputException( Constants::ERROR_INVALID_CRUD_ENTITY );
		}

		return $this->render( $instance->getDetailsTemplate(), [ 'entity' => $instance ] );

	}
}
