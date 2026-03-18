<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Tests\EmailValidator;

use Egulias\EmailValidator\EmailLexer;
use PHPUnit\Framework\TestCase;

class EmailLexerTest extends TestCase
{
    public function testLexerExtendsLib()
    {
        $lexer = new EmailLexer();
        $this->assertInstanceOf('Doctrine\Common\Lexer\AbstractLexer', $lexer);
    }

    /**
     *  @dataProvider getTokens
     *
     */
    public function testLexerTokens($str, $token)
    {
        $lexer = new EmailLexer();
        $lexer->setInput($str);
        $lexer->moveNext();
        $lexer->moveNext();
        $this->assertEquals($token, $lexer->current->type);
    }

    public function testLexerParsesMultipleSpaces()
    {
        $lexer = new EmailLexer();
        $lexer->setInput('  ');
        $lexer->moveNext();
        $lexer->moveNext();
        $this->assertEquals(EmailLexer::S_SP, $lexer->current->type);
        $lexer->moveNext();
        $this->assertEquals(EmailLexer::S_SP, $lexer->current->type);
    }

    /**
     * @dataProvider invalidUTF8CharsProvider
     */
    public function testLexerParsesInvalidUTF8($char)
    {
        $lexer = new EmailLexer();
        $lexer->setInput($char);
        $lexer->moveNext();
        $lexer->moveNext();

        $this->assertEquals(EmailLexer::INVALID, $lexer->current->type);
    }

    public static function invalidUTF8CharsProvider()
    {
        $chars = [];
        for ($i = 0; $i < 0x100; ++$i) {
            $c = self::utf8Chr($i);
            if (preg_match('/(?=\p{Cc})(?=[^\t\n\n\r])/u', $c) && !preg_match('/\x{0000}/u', $c)) {
                $chars[] = [$c];
            }
        }

        return $chars;
    }

    protected static function utf8Chr($code_point)
    {

        if ($code_point < 0 || 0x10FFFF < $code_point || (0xD800 <= $code_point && $code_point <= 0xDFFF)) {
            return '';
        }

        if ($code_point < 0x80) {
            $hex[0] = $code_point;
            $ret = chr($hex[0]);
        } elseif ($code_point < 0x800) {
            $hex[0] = 0x1C0 | $code_point >> 6;
            $hex[1] = 0x80  | $code_point & 0x3F;
            $ret = chr($hex[0]) . chr($hex[1]);
        } elseif ($code_point < 0x10000) {
            $hex[0] = 0xE0 | $code_point >> 12;
            $hex[1] = 0x80 | $code_point >> 6 & 0x3F;
            $hex[2] = 0x80 | $code_point & 0x3F;
            $ret = chr($hex[0]) . chr($hex[1]) . chr($hex[2]);
        } else {
            $hex[0] = 0xF0 | $code_point >> 18;
            $hex[1] = 0x80 | $code_point >> 12 & 0x3F;
            $hex[2] = 0x80 | $code_point >> 6 & 0x3F;
            $hex[3] = 0x80 | $code_point  & 0x3F;
            $ret = chr($hex[0]) . chr($hex[1]) . chr($hex[2]) . chr($hex[3]);
        }

        return $ret;
    }

    public function testLexerForTab()
    {
        $lexer = new EmailLexer();
        $lexer->setInput("foo\tbar");
        $lexer->moveNext();
        $lexer->skipUntil(EmailLexer::S_HTAB);
        $lexer->moveNext();
        $this->assertEquals(EmailLexer::S_HTAB, $lexer->current->type);
    }

    public function testLexerForUTF8()
    {
        $lexer = new EmailLexer();
        $lexer->setInput('áÇ@bar.com');
        $lexer->moveNext();
        $lexer->moveNext();
        $this->assertEquals(EmailLexer::GENERIC, $lexer->current->type);
        $lexer->moveNext();
        $this->assertEquals(EmailLexer::GENERIC, $lexer->current->type);
    }

    public function testLexerSearchToken()
    {
        $lexer = new EmailLexer();
        $lexer->setInput("foo\tbar");
        $lexer->moveNext();
        $this->assertTrue($lexer->find(EmailLexer::S_HTAB));
    }

    public static function getTokens()
    {
        return [
            ['foo', EmailLexer::GENERIC],
            ["\r", EmailLexer::S_CR],
            ["\t", EmailLexer::S_HTAB],
            ["\r\n", EmailLexer::CRLF],
            ["\n", EmailLexer::S_LF],
            [' ', EmailLexer::S_SP],
            ['@', EmailLexer::S_AT],
            ['IPv6', EmailLexer::S_IPV6TAG],
            ['::', EmailLexer::S_DOUBLECOLON],
            [':', EmailLexer::S_COLON],
            ['.', EmailLexer::S_DOT],
            ['"', EmailLexer::S_DQUOTE],
            ['`', EmailLexer::S_BACKTICK],
            ["'", EmailLexer::S_SQUOTE],
            ['-', EmailLexer::S_HYPHEN],
            ['\\', EmailLexer::S_BACKSLASH],
            ['/', EmailLexer::S_SLASH],
            ['(', EmailLexer::S_OPENPARENTHESIS],
            [')', EmailLexer::S_CLOSEPARENTHESIS],
            ['<', EmailLexer::S_LOWERTHAN],
            ['>', EmailLexer::S_GREATERTHAN],
            ['[', EmailLexer::S_OPENBRACKET],
            [']', EmailLexer::S_CLOSEBRACKET],
            [';', EmailLexer::S_SEMICOLON],
            [',', EmailLexer::S_COMMA],
            ['<', EmailLexer::S_LOWERTHAN],
            ['>', EmailLexer::S_GREATERTHAN],
            ['{', EmailLexer::S_OPENCURLYBRACES],
            ['}', EmailLexer::S_CLOSECURLYBRACES],
            ['|', EmailLexer::S_PIPE],
            ['~', EmailLexer::S_TILDE],
            ['=', EmailLexer::S_EQUAL],
            ['+', EmailLexer::S_PLUS],
            ['¿', EmailLexer::INVERT_QUESTIONMARK],
            ['?', EmailLexer::QUESTIONMARK],
            ['#', EmailLexer::NUMBER_SIGN],
            ['¡', EmailLexer::INVERT_EXCLAMATION],
            ['_', EmailLexer::S_UNDERSCORE],
            ['',  EmailLexer::S_EMPTY],
            [chr(31),  EmailLexer::INVALID],
            [chr(226),  EmailLexer::GENERIC],
            [chr(0),  EmailLexer::C_NUL],
        ];
    }

    public function testRecordIsOffAtStart()
    {
        $lexer = new EmailLexer();
        $lexer->setInput('foo-bar');
        $lexer->moveNext();
        $this->assertEquals('', $lexer->getAccumulatedValues());
    }

    public function testRecord()
    {
        $lexer = new EmailLexer();
        $lexer->setInput('foo-bar');
        $lexer->startRecording();
        $lexer->moveNext();
        $lexer->moveNext();
        $this->assertEquals('foo', $lexer->getAccumulatedValues());
    }

    public function testRecordAndClear()
    {
        $lexer = new EmailLexer();
        $lexer->setInput('foo-bar');
        $lexer->startRecording();
        $lexer->moveNext();
        $lexer->moveNext();
        $lexer->clearRecorded();
        $this->assertEquals('', $lexer->getAccumulatedValues());
    }
}
