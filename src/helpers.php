<?php

if (!function_exists('ask')) {
    function ask($question, $payload = []) {
        $data = question();
        $answers = $data->data[$question] ?? [];

        $results = [];
        foreach ($answers['pre'] ?? [] as $groups) {
            foreach ($groups as [$callback, $haltIf, $haltGroup]) {
                $result = call_user_func($callback, $payload);

                $results[] = $result;

                if ($haltGroup === $result) {
                    break;
                }

                if ($haltIf === $result) {
                    return $result;
                }
            }
        }

        foreach ($answers['body'] ?? [] as $groups) {
            foreach ($groups as [$callback, $haltIf, $haltGroup]) {
                $result = call_user_func($callback, $payload);

                $results[] = $result;

                if ($haltGroup === $result) {
                    break;
                }

                if ($haltIf === $result) {
                    return $result;
                }
            }
        }

        foreach ($results as $result) {
            if ($result === false) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('question')) {
    function question($question = null, $group = null) {

        static $chain;

        if (is_null($question)) {
            return $chain;
        }

        if ($chain) {
            $chain->__construct([$question, $group]);

            return $chain;
        }

        return $chain = new class ($question) {

            public $data = [];

            public $question;

            public function __construct($question)
            {
                $this->question = $question;
            }

            public function askFrom($callback, $append = false)
            {
                return $this->set($callback, $append, 'body');
            }

            public function preAskFrom($callback, $append = false)
            {
                return $this->set($callback, $append, 'pre');
            }

            public function set($callback, $append, $stage)
            {
                return new class ($this, [$callback, $append, $stage]) {

                    public $haltIf = '1|(&_&)|1';

                    public $haltGroupIf = '1|(&_&)|1';

                    public function __construct($chain, $data)
                    {
                        $this->data = $data;
                        $this->chain = $chain;
                    }

                    public function __destruct()
                    {
                        [$callback, $append, $stage] = $this->data;
                        $callback = [$callback, $this->haltIf, $this->haltGroupIf];
                        $chain = $this->chain;
                        [$question, $group] = $chain->question;
                        $group = $group ?: '|default_grp|';

                        if (isset($chain->data[$question][$stage][$group])) {
                            if ($append) {
                                array_unshift($chain->data[$question][$stage][$group], $callback);
                            } else {
                                $chain->data[$question][$stage][$group][] = $callback;
                            }
                        } else {
                            $chain->data[$question][$stage][$group] = [$callback];
                        }
                    }

                    public function haltIf($value)
                    {
                        $this->haltIf = $value;
                    }

                    public function haltGroupIf($value)
                    {
                        $this->haltGroupIf = $value;
                    }
                };
            }

        };
    }
}
