<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Users\User\Type\Id;

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class UserUid extends Uid
{
    public const string TEST = '018ae5c6-eb00-7a96-9ebf-d1be4bb044c4';

    public const string TEST _USER = '018549ea-0ff7-7439-872e-dbda9993e413';

    public const string TEST _ADMIN = '018549ee-24ba-7177-9622-d6d8f7b721ee';

    public const string TEST _MODER = '0187a420-e616-7c12-8ddd-ff0527f3cba1';

    public const string TYPE = 'user_id';

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