<?php

namespace App;

enum PaymentStatus:int
{
    case Payed=1;
    case Pending=2;
    case Cancelled=0;
}
