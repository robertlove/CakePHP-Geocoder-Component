<?php
/**
 * GeocoderComponent
 *
 * This component is used for performing forward or reverse geocoding operations
 * using The Google Geocoding Web Service
 * http://code.google.com/apis/maps/documentation/geocoding/
 *
 */
class GeocoderComponent extends Object
{
    /**
     * The parameters passed to the web service
     *
     * @var array
     * @access protected
     */
    protected $_params = array();

    /**
     * The query string version of $_params
     *
     * @var string
     * @access protected
     */
    protected $_query = null;

    /**
     * The request URL
     *
     * @var string
     * @access protected
     */
    protected $_url = null;

    /**
     * The address that you want to geocode
     *
     * Note: You may pass either an address OR a latlng to lookup.
     * If you pass a latlng, the geocoder performs what is known as a reverse
     * geocode.
     *
     * @var string
     * @access public
     */
    public $address = null;

    /**
     * The bounding box of the viewport within which to bias geocode results
     * more prominently.
     * For more information see Viewport Biasing:
     * http://code.google.com/apis/maps/documentation/geocoding/#Viewports
     *
     * @var string
     * @access public
     */
    public $bounds = null;

    /**
     * The language in which to return results. See the supported list of domain
     * languages:
     * http://code.google.com/apis/maps/faq.html#languagesupport
     *
     * Note that Google often update supported languages so this list may
     * not be exhaustive. If language is not supplied, the geocoder will attempt
     * to use the native language of the domain from which the request is sent
     * wherever possible.
     *
     * @var string
     * @access public
     */
    public $language = null;

    /**
     * The latitude value
     *
     * @var float
     * @access public
     */
    public $latitude = null;

    /**
     * The textual latitude/longitude value for which you wish to obtain the
     * closest, human-readable address.
     *
     * Note: You may pass either an address OR a latlng to lookup.
     * If you pass a latlng, the geocoder performs what is known as a reverse
     * geocode.
     *
     * @var string
     * @access public
     */
    public $latlng = null;

    /**
     * The longitude value
     *
     * @var float
     * @access public
     */
    public $longitude = null;

    /**
     * The region code, specified as a ccTLD ("top-level domain") two-character
     * value. (For more information see Region Biasing:
     * http://code.google.com/apis/maps/documentation/geocoding/#RegionCodes
     *
     * @var string
     * @access public
     */
    public $region = null;

    /**
     * The raw JSON web service response
     *
     * @var string
     * @access public
     */
    public $response = null;

    /**
     * The result object
     *
     * @var object
     * @access public
     */
    public $result = null;

    /**
     * Indicates whether or not the geocoding request comes from a device with a location sensor. This value must be either true or false.
     *
     * @var boolean
     * @access public
     */
    public $sensor = false;

    /**
     * The status of the request
     * May contain debugging information to help you track down why Geocoding is
     * not working. See:
     * http://code.google.com/apis/maps/documentation/geocoding/#StatusCodes
     *
     * @var string
     * @access public
     */
    public $status = null;

    /**
     * Perform a geocoding request
     *
     * @return object $this
     * @access public
     */
    public function geocode()
    {
        $this->_params = array(
            'address' => $this->address,
            'bounds' => $this->bounds,
            'language' => $this->language,
            'latlng' => $this->latlng,
            'region' => $this->region,
            'sensor' => $this->sensor
        );
        $this->_params['sensor'] = ($this->sensor === true) ? 'true' : 'false';
        if ($this->_query = http_build_query($this->_params)) {
            if ($this->_url = 'http://maps.google.com/maps/api/geocode/json?' . $this->_query) {
                if ($this->response = file_get_contents($this->_url)) {
                    if ($result = json_decode($this->response)) {
                        if (isset($result->status)) {
                            $this->status = $result->status;
                        }
                        if (isset($result->results)) {
                            $this->result = $result->results;
                            if (is_null($this->address)) {
                                if (isset($result->results[0]->formatted_address)) {
                                    $this->address = $result->results[0]->formatted_address;
                                }
                            }
                            if (is_null($this->bounds)) {
                                if (isset($result->results[0]->geometry->viewport->southwest->lat)) {
                                    if (isset($result->results[0]->geometry->viewport->southwest->lng)) {
                                        if (isset($result->results[0]->geometry->viewport->northeast->lat)) {
                                            if (isset($result->results[0]->geometry->viewport->northeast->lng)) {
                                                $this->bounds = $result->results[0]->geometry->viewport->southwest->lat . ',' . $result->results[0]->geometry->viewport->southwest->lat . '|' . $result->results[0]->geometry->viewport->northeast->lat . ',' . $result->results[0]->geometry->viewport->northeast->lng;
                                            }
                                        }
                                    }
                                }
                            }
                            if (is_null($this->latitude)) {
                                if (isset($result->results[0]->geometry->location->lat)) {
                                    $this->latitude = (float) $result->results[0]->geometry->location->lat;
                                }
                            }
                            if (is_null($this->longitude)) {
                                if (isset($result->results[0]->geometry->location->lng)) {
                                    $this->longitude = (float) $result->results[0]->geometry->location->lng;
                                }
                            }
                            if (is_null($this->latlng)) {
                                if (!is_null($this->latitude)) {
                                    if (!is_null($this->longitude)) {
                                        $this->latlng = $this->latitude . ',' . $this->longitude;
                                    }
                                }
                                $this->latlng = $this->latitude . ',' . $this->longitude;
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Initialize component
     *
     * @param array $options Array of internal variables to set on instantiation
     * @access public
     */
    public function initialize(&$controller, $settings = array())
    {
        $this->_set($settings);
    }

    /**
     * Reset all internal variables
     *
     * @return object $this
     * @access public
     */
    public function reset()
    {
        $this->_params = array();
        $this->_query = null;
        $this->_url = null;
        $this->address = null;
        $this->bounds = null;
        $this->language = null;
        $this->latitude = null;
        $this->latlng = null;
        $this->longitude = null;
        $this->region = null;
        $this->response = null;
        $this->result = null;
        $this->sensor = false;
        $this->status = null;
        return $this;
    }
}