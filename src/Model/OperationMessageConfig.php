<?php


namespace Codyas\Toolbox\Model;


class OperationMessageConfig
{

	private $onRegistrationTitle;
	private $onRegistrationBody;
	private $onUpdateTitle;
	private $onUpdateBody;
	private $onErrorTitle;
	private $onErrorBody;

	/**
	 * @return mixed
	 */
	public function getOnRegistrationTitle()
	{
		return $this->onRegistrationTitle;
	}

	/**
	 * @param mixed $onRegistrationTitle
	 *
	 * @return OperationMessageConfig
	 */
	public function setOnRegistrationTitle( $onRegistrationTitle )
	{
		$this->onRegistrationTitle = $onRegistrationTitle;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOnRegistrationBody()
	{
		return $this->onRegistrationBody;
	}

	/**
	 * @param mixed $onRegistrationBody
	 *
	 * @return OperationMessageConfig
	 */
	public function setOnRegistrationBody( $onRegistrationBody )
	{
		$this->onRegistrationBody = $onRegistrationBody;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOnUpdateTitle()
	{
		return $this->onUpdateTitle;
	}

	/**
	 * @param mixed $onUpdateTitle
	 *
	 * @return OperationMessageConfig
	 */
	public function setOnUpdateTitle( $onUpdateTitle )
	{
		$this->onUpdateTitle = $onUpdateTitle;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOnUpdateBody()
	{
		return $this->onUpdateBody;
	}

	/**
	 * @param mixed $onUpdateBody
	 *
	 * @return OperationMessageConfig
	 */
	public function setOnUpdateBody( $onUpdateBody )
	{
		$this->onUpdateBody = $onUpdateBody;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOnErrorTitle()
	{
		return $this->onErrorTitle;
	}

	/**
	 * @param mixed $onErrorTitle
	 *
	 * @return OperationMessageConfig
	 */
	public function setOnErrorTitle( $onErrorTitle )
	{
		$this->onErrorTitle = $onErrorTitle;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getOnErrorBody()
	{
		return $this->onErrorBody;
	}

	/**
	 * @param mixed $onErrorBody
	 *
	 * @return OperationMessageConfig
	 */
	public function setOnErrorBody( $onErrorBody )
	{
		$this->onErrorBody = $onErrorBody;

		return $this;
	}


}