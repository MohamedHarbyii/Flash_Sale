<?php

namespace App;

enum PaymentStatus:int
{
    case Paid=1;
    case Pending=2;
    case Cancelled=0;
}
