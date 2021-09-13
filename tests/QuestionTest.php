<?php

namespace Tests;

use Illuminate\Foundation\Auth\User;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    public function test_basic_functionality()
    {
        question('can_delete_comment?')
            ->preAskFrom(fn() => true)->haltIf(false);

        question('can_delete_comment?')
            ->askFrom(fn() => true);

        question('can_delete_comment?')
            ->preAskFrom(fn() => false);

        $results = ask('can_delete_comment?');

        $this->assertFalse($results);
    }

    public function test_basic_functionality_groups()
    {
        question('can_delete_comment?')
            ->askFrom(fn() => true);

        question('can_delete_comment?', 'ownership')
            ->askFrom(fn() => true)->haltGroupIf(true);

        question('can_delete_comment?', 'ownership')
            ->askFrom(fn() => false);

        $results = ask('can_delete_comment?');

        $this->assertTrue($results);
    }
}