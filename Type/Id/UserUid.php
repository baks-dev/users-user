<?php

namespace BaksDev\Users\User\Type\Id;

use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class UserUid extends Uid
{
	public const TYPE = 'user_id';
	
	private mixed $option;
	
	
	public function __construct(
		AbstractUid|string|null $value = null,
		mixed $option = null,
	)
	{
		parent::__construct($value);
		$this->option = $option;
	}
	
	
	public function getOption() : mixed
	{
		return $this->option;
	}
	
}