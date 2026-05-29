<?php
namespace API;

abstract class BaseController
{
    protected $db;
    protected $table;
    protected $validationRules = [];
    protected $requestData = [];

    public function __construct($database)
    {
        $this->db = $database;
        $this->parseRequestData();
    }

    protected function parseRequestData()
    {
        $input = file_get_contents('php://input');
        
        if (!empty($input)) {
            $this->requestData = json_decode($input, true) ?? [];
        }

        $this->requestData = array_merge($_GET, $this->requestData);
    }

    protected function input($key = null, $default = null)
    {
        if ($key === null) {
            return $this->requestData;
        }
        
        return $this->requestData[$key] ?? $default;
    }
    protected function validate($data = null)
    {
        $data = $data ?? $this->requestData;
        
        if (empty($this->validationRules)) {
            return true;
        }
        
        $errors = [];
        
        foreach ($this->validationRules as $field => $rules) {
            $rules = explode('|', $rules);
            $value = $data[$field] ?? null;
            
            foreach ($rules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $errors[$field] = "$field is required";
                } elseif ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "$field must be a valid email";
                } elseif ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field] = "$field must be numeric";
                } elseif (strpos($rule, 'min:') === 0 && !empty($value)) {
                    $min = (int)substr($rule, 4);
                    if (strlen((string)$value) < $min) {
                        $errors[$field] = "$field must be at least $min characters";
                    }
                } elseif (strpos($rule, 'max:') === 0 && !empty($value)) {
                    $max = (int)substr($rule, 4);
                    if (strlen((string)$value) > $max) {
                        $errors[$field] = "$field must not exceed $max characters";
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception("Validation failed", 422);
        }
        
        return true;
    }

    abstract public function index();
    abstract public function show($id);
    abstract public function store();
    abstract public function update($id);
    abstract public function destroy($id);
}
?>
