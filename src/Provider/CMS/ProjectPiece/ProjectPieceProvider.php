<?php

declare(strict_types=1);

namespace App\Provider\CMS\ProjectPiece;

class ProjectPieceProvider
{
    public const PIECE_TYPE_BATHROOM = 'bathroom';
    public const PIECE_TYPE_KITCHEN = 'kitchen';
    public const PIECE_TYPE_ROOM = 'room';
    public const PIECE_TYPE_OTHER = 'other';

    public const ALLOWED_PIECES_TYPES = [
        self::PIECE_TYPE_KITCHEN,
        self::PIECE_TYPE_ROOM,
        self::PIECE_TYPE_BATHROOM,
        self::PIECE_TYPE_OTHER,
    ];

    public function getPiecesTypeChoices(): array
    {
        return self::ALLOWED_PIECES_TYPES;
    }
}
