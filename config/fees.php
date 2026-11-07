<?php
/**
 * Fee rules. Change the numbers here; every page reads from this file.
 *
 *   Tuition  = total units × PER_UNIT
 *   Lab      = LAB_FEE for each subject with a lab
 *   Tutorial = TUTORIAL_FEE for each subject with a tutorial
 *   Fixed    = ENTRANCE + INTERNET + MISC (charged once per enrollment)
 */
declare(strict_types=1);

const FEE_PER_UNIT     = 296.00;
const FEE_LAB          = 1500.00;
const FEE_TUTORIAL     = 1500.00;
const FEE_ENTRANCE     = 2050.00;
const FEE_INTERNET     = 150.00;
const FEE_MISC         = 50.00;
const FEE_CURRENCY     = '₱';
