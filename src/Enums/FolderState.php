<?php

namespace ProjectSaturnStudios\XFer\Enums;

enum FolderState: string
{
    case UNDISCOVERED = 'UNDISCOVERED';
    case FOUND = 'FOUND';
    case NOT_PRESENT = 'NOT_PRESENT';

}
