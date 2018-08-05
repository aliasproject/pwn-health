<?php namespace AliasProject\PWNHealth;

use SimpleXMLElement;
use Log;

class PWNHealth
{
    const CLIENT_ENDPOINT = 'https://api16-staging.pwnhealth.com/';
    const LAB_ENDPOINT = 'https://api13-staging.pwnhealth.com/';

    private $username;
    private $password;

    public function __construct()
    {
        $this->username = config('pwnhealth.username');
        $this->password = config('pwnhealth.password');
    }

    /**
     * Create new order
     *
     * @param string $first_name
     * @param string $last_name
     * @param string $dob
     * @param string $gender
     * @param string $email
     * @param string $address
     * @param string $city
     * @param string $state
     * @param int $zip
     * @param string $work_phone
     * @param array $test_types
     * @param bool $take_tests_same_day
     * @return \Illuminate\Http\Response
     */
    public function createOrder(string $first_name, string $last_name, string $dob, string $gender, string $email, string $address, string $city, string $state, int $zip, string $work_phone, array $test_types, bool $take_tests_same_day)
    {
        // Set Headers
        $headers = ['Content-Type: application/xml'];

        // Format DOB
        $dob = date('Ymd', strtotime($dob));

        // Generate XML
        $customerXml = $this->generateCustomerXML($first_name, $last_name, $dob, $gender, $email, $address, $city, $state, $zip, $work_phone, $test_types, $take_tests_same_day);

        // Make request
        $createCustomer = $this->makeRequest(self::CLIENT_ENDPOINT . '/customers', (string) $customerXml, $headers, true);

        // Convert XML to JSON
        $xml = simplexml_load_string($createCustomer);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    public function getOrders(string $status="all", $start_date, $end_date, int $page=0, int $per_page=10)
    {
        // Make request
        $test_types = makeRequest(self::CLIENT_ENDPOINT . '/customers?status=' . $status . '&start_date=' . $start_date . '&end_date=' . $end_date . '&page=' . $page . '&per_page=' . $per_page);

        // Convert XML to JSON
        $xml = simplexml_load_string($test_types);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    public function getOrderDetails(string $order_id, $include)
    {
        // Make request
        $test_types = makeRequest(self::CLIENT_ENDPOINT . '/customers/' . $order_id . '?include=' . $include);

        // Convert XML to JSON
        $xml = simplexml_load_string($test_types);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    public function getRegisteredLabs(int $lab_id)
    {
        // Make request
        $test_types = makeRequest(self::CLIENT_ENDPOINT . '/registered_labs?lab_id=' . $lab_id);

        // Convert XML to JSON
        $xml = simplexml_load_string($test_types);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    public function getTestTypes(int $lab_id)
    {
        // Make request
        $test_types = makeRequest(self::CLIENT_ENDPOINT . '/test_types?lab_id=' . $lab_id);

        // Convert XML to JSON
        $xml = simplexml_load_string($test_types);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    public function getTestGroups(int $lab_id, int $account_number, string $name='')
    {
        // Make request
        $test_types = makeRequest(self::CLIENT_ENDPOINT . '/test_groups?lab_id=' . $lab_id . '&account_number=' . $account_number . 'name=' . $name);

        // Convert XML to JSON
        $xml = simplexml_load_string($test_types);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    /**
     * Get Registered Labs to Account
     *
     * @return string
     */
    function registeredLabs()
    {
        // Make request
        $response = makeRequest(self::CLIENT_ENDPOINT . '/registered_labs');

        // Convert XML to JSON
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    /**
     * Get Registered Labs by Zip Code
     *
     * @param  integer  $zip
     * @param  integer  $lab (Optional)
     * @param  integer  $limit (Optional - Default: 10)
     * @return string
     */
    function findPSC(int $zip, int $lab=NULL, int $limit=10)
    {
        // Make request
        $response = makeRequest(self::CLIENT_ENDPOINT . '/find_psc/' . $zip . '?lab=' . $lab . '&limit=' . $limit);

        // Convert XML to JSON
        $xml = simplexml_load_string($response);
        $json = json_encode($xml);

        // Return Results
        return json_decode($json, TRUE);
    }

    private function generateCustomerXML(string $first_name, string $last_name, int $dob, string $gender, string $email, string $address, string $city, string $state, int $zip, string $work_phone, array $test_types, bool $take_tests_same_day)
    {
        // Build XML
        $xml = new SimpleXMLElement('<customer/>');
        $xml->addChild('first_name', $first_name);
        $xml->addChild('last_name', $last_name);
        $xml->addChild('dob', $dob);
        $xml->addChild('gender', $gender);
        $xml->addChild('email', $email);
        $xml->addChild('address', $address);
        $xml->addChild('city', $city);
        $xml->addChild('state', $state);
        $xml->addChild('zip', $zip);
        $xml->addChild('work_phone', $work_phone);
        $xml->addChild('test_types', implode(',', $test_types));
        $xml->addChild('take_tests_same_day', $take_tests_same_day);

        //Header('Content-type: application/xml');
        return $xml->asXML();
    }

    /**
     * Make HTTP Request
     *
     * @param  string  $url
     * @return string
     */
    private function makeRequest(string $url, string $data='', array $headers=[], bool $post=false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $post);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
