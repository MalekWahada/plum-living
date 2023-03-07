<?php

declare(strict_types=1);

namespace App\Validator\PhoneNumberConstraint;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber as PhoneNumberObject;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Form\Form;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This class is taken from Misd\PhoneNumberBundle in order to change the validation process.
 * The original class did not take into account the country field of form
 */
class PhoneNumberValidator extends ConstraintValidator
{
    private ?PhoneNumberUtil $phoneUtil;
    private string $defaultRegion;
    private ?PropertyAccessorInterface $propertyAccessor = null;
    private int $format;

    public function __construct(PhoneNumberUtil $phoneUtil = null, string $defaultRegion = PhoneNumberUtil::UNKNOWN_REGION, int $format = PhoneNumberFormat::INTERNATIONAL)
    {
        $this->phoneUtil = $phoneUtil;
        $this->defaultRegion = $defaultRegion;
        $this->format = $format;
    }

    /**
     * @phpstan-param PhoneNumber $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_scalar($value) && !(\is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (false === $value instanceof PhoneNumberObject) {
            $value = (string) $value;

            try {
                $phoneNumber = $this->phoneUtil->parse($value, $this->getRegion($constraint));
            } catch (NumberParseException $e) {
                $this->addViolation($value, $constraint);

                return;
            }
        } else {
            $phoneNumber = $value;
            $value = $this->phoneUtil->format($phoneNumber, $constraint->format ?? $this->format);
        }

        if (false === $this->phoneUtil->isValidNumber($phoneNumber)) {
            $this->addViolation($value, $constraint);

            return;
        }

        $validTypes = $this->getValidTypes($constraint->getTypes());

        if (!empty($validTypes)) {
            $type = $this->phoneUtil->getNumberType($phoneNumber);

            if (!\in_array($type, $validTypes, true)) {
                $this->addViolation($value, $constraint);
            }
        }
    }

    /**
     * @phpstan-param PhoneNumber $constraint
     */
    private function getRegion(Constraint $constraint): ?string
    {
        $region = null;
        if (null !== $path = $constraint->regionPath) {
            $object = $this->context->getObject();
            if (null === $object) {
                throw new \LogicException('The current validation does not concern an object');
            }

            if ($object instanceof Form) { // Case of form validation
                $region = $object->get($constraint->regionPath)->getData();
            } else { // Case of entity validation
                try {
                    $region = $this->getPropertyAccessor()->getValue($object, $path);
                } catch (NoSuchPropertyException $e) {
                    throw new ConstraintDefinitionException(sprintf('Invalid property path "%s" provided to "%s" constraint: ', $path, get_debug_type($constraint)).$e->getMessage(), 0, $e);
                }
            }
        }

        return $region ?? $constraint->defaultRegion ?? $this->defaultRegion;
    }

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    private function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (null === $this->propertyAccessor) {
            if (!class_exists(PropertyAccess::class)) {
                throw new LogicException('Unable to use property path as the Symfony PropertyAccess component is not installed.');
            }
            $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->propertyAccessor;
    }

    /**
     * @phpstan-param PhoneNumber $constraint
     */
    private function addViolation(string $value, Constraint $constraint): void
    {
        $this->context->buildViolation($constraint->getMessage())
            ->setParameter('{{ types }}', implode(', ', $constraint->getTypeNames()))
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setCode(PhoneNumber::INVALID_PHONE_NUMBER_ERROR)
            ->addViolation();
    }

    /**
     * @param string[] $types
     * @return int[]
     */
    private function getValidTypes(array $types): array
    {
        $validTypes = [];
        foreach ($types as $type) {
            switch ($type) {
                case PhoneNumber::FIXED_LINE:
                    $validTypes[] = PhoneNumberType::FIXED_LINE;
                    $validTypes[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
                    break;
                case PhoneNumber::MOBILE:
                    $validTypes[] = PhoneNumberType::MOBILE;
                    $validTypes[] = PhoneNumberType::FIXED_LINE_OR_MOBILE;
                    break;
                case PhoneNumber::PAGER:
                    $validTypes[] = PhoneNumberType::PAGER;
                    break;
                case PhoneNumber::PERSONAL_NUMBER:
                    $validTypes[] = PhoneNumberType::PERSONAL_NUMBER;
                    break;
                case PhoneNumber::PREMIUM_RATE:
                    $validTypes[] = PhoneNumberType::PREMIUM_RATE;
                    break;
                case PhoneNumber::SHARED_COST:
                    $validTypes[] = PhoneNumberType::SHARED_COST;
                    break;
                case PhoneNumber::TOLL_FREE:
                    $validTypes[] = PhoneNumberType::TOLL_FREE;
                    break;
                case PhoneNumber::UAN:
                    $validTypes[] = PhoneNumberType::UAN;
                    break;
                case PhoneNumber::VOIP:
                    $validTypes[] = PhoneNumberType::VOIP;
                    break;
                case PhoneNumber::VOICEMAIL:
                    $validTypes[] = PhoneNumberType::VOICEMAIL;
                    break;
                default:
            }
        }
        return array_unique($validTypes);
    }
}
