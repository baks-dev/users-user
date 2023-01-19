<?php

namespace BaksDev\Users\User\Type\Id;

use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;

final class UserUid
{
    public const TYPE = 'user_id';
    
    private Uuid $value;
    
    private ?string $name;
    
    
    public function __construct(AbstractUid|string|null $value = null, string $name = null)
    {
        if($value === null)
        {
            $value = Uuid::v7();
        }
        
        else if(is_string($value))
        {
            $value = new Uuid($value);
        }
        
        $this->value = $value;
        $this->name = $name;
    }
    
    public function __toString() : string
    {
        return $this->value;
    }
    
    public function getValue() : AbstractUid
    {
        return $this->value;
    }
    
    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    
    public function equals(UserUid $uid) : bool
    {
        return (string) $this->value === (string) $uid->getValue();
    }
    
}