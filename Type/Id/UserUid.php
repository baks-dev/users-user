<?php

namespace BaksDev\Users\User\Type\Id;

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class UserUid extends Uid
{
    public const TEST = '018ae5c6-eb00-7a96-9ebf-d1be4bb044c4';

    public const TEST_USER = '018549ea-0ff7-7439-872e-dbda9993e413';

    public const TEST_ADMIN = '018549ee-24ba-7177-9622-d6d8f7b721ee';

    public const TEST_MODER = '0187a420-e616-7c12-8ddd-ff0527f3cba1';

	public const TYPE = 'user_id';
	
	private mixed $option;
	
	
	public function __construct(
		AbstractUid|self|string|null $value = null,
		mixed $option = null,
	)
	{
        parent::__construct($value);

		$this->option = $option;
	}
	
	
	public function getOption(): mixed
	{
		return $this->option;
	}
	
}