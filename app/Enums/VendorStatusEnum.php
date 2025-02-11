<?php

namespace App\Enums;

enum VendorStatusEnum:string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(){
        return match ($this->value){
            self::Pending->value => __('Pending'),
            self::Approved->value => __('Approved'),
            self::Rejected->value => __('Rejected'),
            default => throw new \Exception('Unexpected match value'),
        };
    }

    public static function labels():array
    {
        return [
            self::Pending->value => __('Pending'),
            self::Approved->value => __('Approved'),
            self::Rejected->value => __('Rejected'),
        ];
    }

    public static function colors():array
    {
        return [
            'gray'=>self::Pending->value,
            'success'=>self::Approved->value,
            'danger'=>self::Rejected->value,
        ];
    }

}
