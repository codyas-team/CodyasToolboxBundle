<?php


namespace Codyas\Toolbox\Model;


use App\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Vich\UploaderBundle\Storage\StorageInterface;

class RowRendererArguments
{
	/** @var TranslatorInterface */
	public $translator;

	/** @var RouterInterface */
	public $router;

	/** @var Environment */
	public $twig;

	/** @var AuthorizationCheckerInterface */
	public $authChecker;

	/** @var UserInterface */
	public $user;

	/**
	 * RowRendererArguments constructor.
	 *
	 * @param TranslatorInterface $translator
	 * @param RouterInterface $router
	 * @param Environment $twig
	 * @param AuthorizationCheckerInterface $authChecker
	 * @param UserInterface $user
	 */
	public function __construct( TranslatorInterface $translator, RouterInterface $router, Environment $twig, AuthorizationCheckerInterface $authChecker, UserInterface $user )
	{
		$this->translator     = $translator;
		$this->router         = $router;
		$this->twig           = $twig;
		$this->authChecker    = $authChecker;
		$this->user           = $user;
	}

	/**
	 * @return TranslatorInterface
	 */
	public function getTranslator(): TranslatorInterface
	{
		return $this->translator;
	}

	/**
	 * @return RouterInterface
	 */
	public function getRouter(): RouterInterface
	{
		return $this->router;
	}

	/**
	 * @return Environment
	 */
	public function getTwig(): Environment
	{
		return $this->twig;
	}

	/**
	 * @return AuthorizationCheckerInterface
	 */
	public function getAuthChecker(): AuthorizationCheckerInterface
	{
		return $this->authChecker;
	}

	/**
	 * @return UserInterface
	 */
	public function getUser(): UserInterface
	{
		return $this->user;
	}


}