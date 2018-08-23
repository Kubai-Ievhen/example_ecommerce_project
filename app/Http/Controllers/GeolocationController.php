<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MenaraSolutions\Geographer\Earth;
use MenaraSolutions\Geographer\Country;
use MenaraSolutions\Geographer\City;
use MenaraSolutions\Geographer\State;
use MenaraSolutions\Geographer;


/**@api GeolocationController
 * @apiName GeolocationController
 * @apiGroup GeolocationController
 * @apiDescription App\Http\Controllers Class GeolocationController
 */
class GeolocationController extends Controller
{
    /** @api {public} getCountrys() getCountrys()
     * @apiName getCountrys()
     * @apiGroup GeolocationController
     * @apiDescription  Get data of countries
     * @apiSuccess {array} countries Array with data of countries
     * @apiSuccessExample Success-Response:
     *  [
     *      {
     *          "code":"AF",
     *          "code3":"AFG",
     *          "isoCode":"AF",
     *          "numericCode":"004",
     *          "geonamesCode":1149361,
     *          "fipsCode":"AF",
     *          "area":647500,
     *          "currency":"AFN",
     *          "phonePrefix":"93",
     *          "population":29121286,
     *          "continent":"AS",
     *          "language":"fa",
     *          "name":"Afghanistan"
     *      },
     *      ..........
     *  ]
     */
    public function getCountrys(){
        $earth = new Earth();
        return $earth->getCountries()->sortBy('name')->toArray();
    }

    /** @api {public} getStates() getStates()
     * @apiName getStates()
     * @apiGroup GeolocationController
     * @apiParam {string} id Code of country
     * @apiDescription  Get data of country State
     * @apiSuccess {array} states Array with data of country State
     * @apiSuccessExample Success-Response:
     *  [
     *      {
     *          "code":1147745,
     *          "fipsCode":"AF01",
     *          "isoCode":"AF-BDS",
     *          "geonamesCode":1147745,
     *          "name":"Badakhshan"
     *      },
     *      {
     *          "code":1147707,
     *          "fipsCode":"AF02",
     *          "isoCode":"AF-BDG",
     *          "geonamesCode":1147707,
     *          "name":"Badghis"
     *      },
     *      ..........
     *  ]
     */
    public function getStates($id){
        $thailand  = Country::build($id);
        return $thailand->getStates()->sortBy('name')->toArray();
    }

    /** @api {public} getCites() getCites()
     * @apiName getCites()
     * @apiGroup GeolocationController
     * @apiParam {int} id Code of state
     * @apiDescription  Get data of state City
     * @apiSuccess {array} cities Array with data of state City
     * @apiSuccessExample Success-Response:
     *  [
     *      {
     *          "code":1817993,
     *          "geonamesCode":1817993,
     *          "name":"Anqing",
     *          "latitude":30.51365,
     *          "longitude":117.04723,
     *          "population":358661
     *      },
     *      ..........
     *  ]
     */
    public function getCites($id){
        $state = State::build($id);
        return $state->getCities()->sortBy('name')->toArray();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCityCode($id){
        $state = City::build($id);
        return $state->getCode();
    }

    /** @api {public} getCountry() getCountry()
     * @apiName getCountry()
     * @apiGroup GeolocationController
     * @apiParam {int} id Code of country
     * @apiDescription  Get data of country
     * @apiSuccess {array} country Array with data of country
     * @apiSuccessExample Success-Response:
     *  {
     *      "code":"CN",
     *      "code3":"CHN",
     *      "isoCode":"CN",
     *      "numericCode":"156",
     *      "geonamesCode":1814991,
     *      "fipsCode":"CH",
     *      "area":9596960,
     *      "currency":"CNY",
     *      "phonePrefix":"86",
     *      "population":1330044000,
     *      "continent":"AS",
     *      "language":"zh",
     *      "name":"China"
     * }
     */
    public function getCountry($id){
        $earth = Country::build($id);
        return $earth->toArray();
    }

    /** @api {public} getState() getState()
     * @apiName getState()
     * @apiGroup GeolocationController
     * @apiParam {int} id Code of state
     * @apiDescription  Get data of state
     * @apiSuccess {array} state Array with data of state
     * @apiSuccessExample Success-Response:
     *  {
     *          "code":1147745,
     *          "fipsCode":"AF01",
     *          "isoCode":"AF-BDS",
     *          "geonamesCode":1147745,
     *          "name":"Badakhshan"
     *  }
     */
    public function getState($id){
        $thailand  = State::build($id);
        return $thailand->toArray();
    }

    /** @api {public} getCite() getCite()
     * @apiName getCite()
     * @apiGroup GeolocationController
     * @apiParam {int} id Code of city
     * @apiDescription  Get data of city
     * @apiSuccess {array} city Array with data of city
     * @apiSuccessExample Success-Response:
     *  {
     *          "code":1817993,
     *          "geonamesCode":1817993,
     *          "name":"Anqing",
     *          "latitude":30.51365,
     *          "longitude":117.04723,
     *          "population":358661
     *  }
     */
    public function getCite($id){
        $state = City::build($id);
        return $state->toArray();
    }
}
