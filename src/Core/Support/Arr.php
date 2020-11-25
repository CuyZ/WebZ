<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Support;

final class Arr
{
    /**
     * @param array|object|mixed|null $subject
     * @return array
     */
    public static function castToArray($subject): array
    {
        if (null === $subject) {
            return [];
        }

        return self::toArrayLoop($subject);
    }

    /**
     * @param array|object|mixed $subject
     * @return array
     */
    private static function toArrayLoop($subject): array
    {
        if (!is_object($subject) && !is_array($subject)) {
            throw new InvalidSubjectException($subject);
        }

        if (is_object($subject)) {
            $subject = (array)$subject;
        }

        /**
         * @var string|int $key
         * @var mixed $value
         */
        foreach ($subject as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $subject[$key] = self::toArrayLoop($value);
            }
        }

        return $subject;
    }
}
