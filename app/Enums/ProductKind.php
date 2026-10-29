<?php

namespace App\Enums;

enum ProductKind: string
{
    case Product = 'product';
    case Book = 'book';
    case Course = 'course';
}
