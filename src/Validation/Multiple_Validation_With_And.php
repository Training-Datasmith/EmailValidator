<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Multiple_Errors;
use Egulias\Email_Validator\Validation\Exception\Empty_Validation_List;
use Egulias\Email_Validator\Warning\Warning;
class Multiple_Validation_With_And implements Email_Validation
{
    /**
     * If one of validations fails, the remaining validations will be skipped.
     * This means MultipleErrors will only contain a single error, the first found.
     */
    public const STOP_ON_ERROR = 0;
    /**
     * All of validations will be invoked even if one of them got failure.
     * So MultipleErrors will contain all causes.
     */
    public const ALLOW_ALL_ERRORS = 1;
    /**
     * @var Warning[]
     */
    private array $warnings = [];
    private ?\Egulias\Email_Validator\Result\Multiple_Errors $error = null;
    /**
     * @param EmailValidation[] $validations The validations.
     * @param int               $mode        The validation mode (one of the constants).
     */
    public function __construct(private readonly array $validations, private readonly int $mode = self::ALLOW_ALL_ERRORS)
    {
        if (count($validations) == 0) {
            throw new Empty_Validation_List();
        }
    }
    /**
     * {@inheritdoc}
     */
    public function is_valid(string $email, Email_Lexer $email_lexer): bool
    {
        $result = true;
        foreach ($this->validations as $validation) {
            $email_lexer->reset();
            $validation_result = $validation->is_valid($email, $email_lexer);
            $result = $result && $validation_result;
            $this->warnings = [...$this->warnings, ...$validation->get_warnings()];
            if (!$validation_result) {
                $this->process_error($validation);
            }
            if ($this->should_stop($result)) {
                break;
            }
        }
        return $result;
    }
    private function init_error_storage(): void
    {
        if (null === $this->error) {
            $this->error = new Multiple_Errors();
        }
    }
    private function process_error(Email_Validation $validation): void
    {
        if (null !== $validation->get_error()) {
            $this->init_error_storage();
            /** @psalm-suppress PossiblyNullReference */
            $this->error->add_reason($validation->get_error()->reason());
        }
    }
    private function should_stop(bool $result): bool
    {
        return !$result && $this->mode === self::STOP_ON_ERROR;
    }
    /**
     * Returns the validation errors.
     */
    public function get_error(): ?Invalid_Email
    {
        return $this->error;
    }
    /**
     * @return Warning[]
     */
    public function get_warnings(): array
    {
        return $this->warnings;
    }
}