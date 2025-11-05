<?php
/**
 * Docket Details Manager
 * Handles all docket_details operations with automatic data syncing from related tables
 * Only doc_no is mandatory; all other fields auto-populate or default to N/A
 */

class DocketDetailsManager {
    private $conn;
    
    public function __construct($db_connection) {
        $this->conn = $db_connection;
        $this->ensureTableExists();
    }
    
    /**
     * Ensure docket_details table exists
     */
    private function ensureTableExists() {
        $sql = file_get_contents(__DIR__ . '/create_docket_details_table.sql');
        if ($sql) {
            mysqli_multi_query($this->conn, $sql);
            // Clear results
            while(mysqli_more_results($this->conn)) {
                mysqli_next_result($this->conn);
            }
        }
    }
    
    /**
     * Insert or Update Docket with Auto-Sync
     * @param array $data - Docket data (only doc_no is mandatory)
     * @return array - Result with success status and docket_id
     */
    public function saveDocket($data) {
        // Validate mandatory field
        if (empty($data['doc_no'])) {
            return ['success' => false, 'error' => 'Docket number is mandatory'];
        }
        
        $doc_no = mysqli_real_escape_string($this->conn, trim($data['doc_no']));
        
        // Check if docket already exists
        $existing = $this->getDocketByNumber($doc_no);
        
        if ($existing) {
            // Update existing docket
            return $this->updateDocket($doc_no, $data);
        } else {
            // Insert new docket
            return $this->insertDocket($data);
        }
    }
    
    /**
     * Insert new docket with auto-sync
     */
    private function insertDocket($data) {
        // Auto-sync related data
        $syncedData = $this->autoSyncData($data);
        
        // Build INSERT query
        $fields = [];
        $values = [];
        
        foreach ($syncedData as $key => $value) {
            $fields[] = "`$key`";
            if ($value === null) {
                $values[] = "NULL";
            } else if (is_numeric($value)) {
                $values[] = $value;
            } else {
                $values[] = "'" . mysqli_real_escape_string($this->conn, $value) . "'";
            }
        }
        
        $sql = "INSERT INTO `docket_details` (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $values) . ")";
        
        if (mysqli_query($this->conn, $sql)) {
            return [
                'success' => true,
                'docket_id' => mysqli_insert_id($this->conn),
                'doc_no' => $data['doc_no']
            ];
        } else {
            return [
                'success' => false,
                'error' => mysqli_error($this->conn)
            ];
        }
    }
    
    /**
     * Update existing docket with auto-sync
     */
    private function updateDocket($doc_no, $data) {
        // Auto-sync related data
        $syncedData = $this->autoSyncData($data);
        
        // Remove doc_no from update (it's the identifier)
        unset($syncedData['doc_no']);
        
        // Build UPDATE query
        $setParts = [];
        foreach ($syncedData as $key => $value) {
            if ($value === null) {
                $setParts[] = "`$key` = NULL";
            } else if (is_numeric($value)) {
                $setParts[] = "`$key` = $value";
            } else {
                $setParts[] = "`$key` = '" . mysqli_real_escape_string($this->conn, $value) . "'";
            }
        }
        
        $sql = "UPDATE `docket_details` SET " . implode(', ', $setParts) . " 
                WHERE `doc_no` = '" . mysqli_real_escape_string($this->conn, $doc_no) . "'";
        
        if (mysqli_query($this->conn, $sql)) {
            $existing = $this->getDocketByNumber($doc_no);
            return [
                'success' => true,
                'docket_id' => $existing['docket_id'],
                'doc_no' => $doc_no,
                'updated' => true
            ];
        } else {
            return [
                'success' => false,
                'error' => mysqli_error($this->conn)
            ];
        }
    }
    
    /**
     * Auto-sync data from related tables
     */
    private function autoSyncData($data) {
        $synced = [
            'doc_no' => $data['doc_no'] ?? null
        ];
        
        // Basic fields with defaults
        $synced['trip_group_id'] = $data['trip_group_id'] ?? null;
        $synced['manifest_id'] = $data['manifest_id'] ?? null;
        $synced['service_type'] = $data['service_type'] ?? 'Standard';
        $synced['doc_type'] = $data['doc_type'] ?? 'DRS';
        $synced['status'] = $data['status'] ?? 'pending';
        
        // Dates
        if (!empty($data['pickup_datetime'])) {
            $synced['pickup_datetime'] = $data['pickup_datetime'];
        }
        if (!empty($data['delivery_datetime'])) {
            $synced['delivery_datetime'] = $data['delivery_datetime'];
        }
        
        // Auto-sync Company data
        if (!empty($data['company_id'])) {
            $company = $this->getCompanyDetails($data['company_id']);
            $synced['company_id'] = $data['company_id'];
            $synced['company_name'] = $company['company_title'] ?? 'N/A';
            $synced['company_email'] = $company['company_email'] ?? 'N/A';
            $synced['company_phone'] = $company['company_phone'] ?? 'N/A';
            $synced['company_address'] = $company['company_address'] ?? $data['company_address'] ?? null;
            $synced['pickup_location'] = $data['pickup_location'] ?? $company['company_address'] ?? null;
        } else {
            $synced['company_name'] = $data['company_name'] ?? 'N/A';
            $synced['company_address'] = $data['company_address'] ?? null;
            $synced['pickup_location'] = $data['pickup_location'] ?? $data['company_address'] ?? null;
        }
        
        // Client data
        $synced['client_id'] = $data['client_id'] ?? null;
        $synced['client_name'] = $data['client_name'] ?? 'N/A';
        $synced['client_phone'] = $data['client_phone'] ?? 'N/A';
        $synced['client_email'] = $data['client_email'] ?? 'N/A';
        $synced['client_address'] = $data['client_address'] ?? null;
        $synced['delivery_location'] = $data['delivery_location'] ?? $data['client_address'] ?? null;
        
        // Auto-sync Car data
        if (!empty($data['car_id'])) {
            $car = $this->getCarDetails($data['car_id']);
            $synced['car_id'] = $data['car_id'];
            $synced['car_number'] = $car['car_number'] ?? 'N/A';
            $synced['car_model'] = $car['car_model'] ?? 'N/A';
            $synced['rented_car'] = $car['rented_car'] ?? 0;
        } else {
            $synced['car_number'] = $data['car_number'] ?? 'N/A';
            $synced['car_model'] = $data['car_model'] ?? 'N/A';
        }
        
        // Auto-sync Driver data
        if (!empty($data['driver_id'])) {
            $driver = $this->getDriverDetails($data['driver_id']);
            $synced['driver_id'] = $data['driver_id'];
            $synced['driver_name'] = $driver['driver_name'] ?? 'N/A';
            $synced['driver_phone'] = $driver['driver_number'] ?? 'N/A';
            $synced['driver_license'] = $driver['driver_license'] ?? 'N/A';
        } else {
            $synced['driver_name'] = $data['driver_name'] ?? 'N/A';
            $synced['driver_phone'] = $data['driver_phone'] ?? 'N/A';
            $synced['driver_license'] = $data['driver_license'] ?? 'N/A';
        }
        
        // Auto-sync Helper data
        if (!empty($data['helper_id'])) {
            $helper = $this->getHelperDetails($data['helper_id']);
            $synced['helper_id'] = $data['helper_id'];
            $synced['helper_name'] = $helper['helper_name'] ?? 'N/A';
            $synced['helper_phone'] = $helper['helper_number'] ?? 'N/A';
        } else {
            $synced['helper_name'] = $data['helper_name'] ?? 'N/A';
            $synced['helper_phone'] = $data['helper_phone'] ?? 'N/A';
        }
        
        // Package information
        $synced['item'] = $data['item'] ?? 'N/A';
        $synced['box'] = intval($data['box'] ?? 0);
        $synced['weight'] = floatval($data['weight'] ?? 0);
        $synced['dimensions'] = $data['dimensions'] ?? 'N/A';
        
        // Financial information
        $synced['rate'] = floatval($data['rate'] ?? 0);
        $synced['amount'] = floatval($data['amount'] ?? 0);
        $synced['unit_price'] = floatval($data['unit_price'] ?? $data['rate'] ?? 0);
        $synced['pay_to'] = floatval($data['pay_to'] ?? 0);
        
        // Billing
        $synced['have_eoa_bill_no'] = intval($data['have_eoa_bill_no'] ?? 0);
        $synced['eoa_bill_no'] = $data['eoa_bill_no'] ?? 'N/A';
        $synced['eway_bill'] = $data['eway_bill'] ?? 'N/A';
        $synced['invoice_no'] = $data['invoice_no'] ?? 'N/A';
        
        // Office
        $synced['office_id'] = $data['office_id'] ?? null;
        $synced['branch_office'] = $data['branch_office'] ?? 'N/A';
        
        // Delivery info
        $synced['reason_of_delay'] = $data['reason_of_delay'] ?? 'N/A';
        $synced['proof_of_delivery'] = $data['proof_of_delivery'] ?? 'N/A';
        $synced['tracking_link'] = $data['tracking_link'] ?? null;
        $synced['delivery_notes'] = $data['delivery_notes'] ?? null;
        
        // Car trip details
        $synced['car_oil_amount'] = floatval($data['car_oil_amount'] ?? 0);
        $synced['car_in_time'] = $data['car_in_time'] ?? null;
        $synced['car_out_time'] = $data['car_out_time'] ?? null;
        
        // Additional
        $synced['special_instructions'] = $data['special_instructions'] ?? null;
        $synced['remarks'] = $data['remarks'] ?? null;
        
        return $synced;
    }
    
    /**
     * Get company details from tbl_company
     */
    private function getCompanyDetails($company_id) {
        $sql = "SELECT * FROM tbl_company WHERE company_id = " . intval($company_id);
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }
    
    /**
     * Get car details from tbl_car
     */
    private function getCarDetails($car_id) {
        $sql = "SELECT * FROM tbl_car WHERE car_id = " . intval($car_id);
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }
    
    /**
     * Get driver details from tbl_driver
     */
    private function getDriverDetails($driver_id) {
        $sql = "SELECT * FROM tbl_driver WHERE driver_id = " . intval($driver_id);
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }
    
    /**
     * Get helper details from tbl_helper
     */
    private function getHelperDetails($helper_id) {
        $sql = "SELECT * FROM tbl_helper WHERE helper_id = " . intval($helper_id);
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }
    
    /**
     * Get docket by number
     */
    public function getDocketByNumber($doc_no) {
        $sql = "SELECT * FROM docket_details WHERE doc_no = '" . 
               mysqli_real_escape_string($this->conn, $doc_no) . "' LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }
    
    /**
     * Get docket by ID
     */
    public function getDocketById($docket_id) {
        $sql = "SELECT * FROM docket_details WHERE docket_id = " . intval($docket_id);
        $result = mysqli_query($this->conn, $sql);
        return $result ? mysqli_fetch_assoc($result) : null;
    }
    
    /**
     * Get all dockets for a trip group
     */
    public function getDocketsByTripGroup($trip_group_id) {
        $sql = "SELECT * FROM docket_details WHERE trip_group_id = '" . 
               mysqli_real_escape_string($this->conn, $trip_group_id) . "' ORDER BY created_at ASC";
        $result = mysqli_query($this->conn, $sql);
        $dockets = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dockets[] = $row;
            }
        }
        return $dockets;
    }
    
    /**
     * Get all dockets for a manifest
     */
    public function getDocketsByManifest($manifest_id) {
        $sql = "SELECT * FROM docket_details WHERE manifest_id = " . intval($manifest_id) . 
               " ORDER BY created_at ASC";
        $result = mysqli_query($this->conn, $sql);
        $dockets = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $dockets[] = $row;
            }
        }
        return $dockets;
    }
    
    /**
     * Delete docket by number
     */
    public function deleteDocket($doc_no) {
        $sql = "DELETE FROM docket_details WHERE doc_no = '" . 
               mysqli_real_escape_string($this->conn, $doc_no) . "'";
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Check if docket exists
     */
    public function docketExists($doc_no) {
        return $this->getDocketByNumber($doc_no) !== null;
    }
}
