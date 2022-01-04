# DSN Monitor

Data visualization for NASA's DSNNow public data.
A live version of the project can be accessed at http://dsnmonitor.ddns.net.

## Description

NASA publishes real-time data about its Deep Space Network and current radio links with deep space missions, in the form of the website [DSNNow](https://eyes.nasa.gov/dsn/dsn.html).
However, a historical visualization of its real-time data is missing. This project is about giving this fascinating data an alternative presentation, from which different patterns and meaning can be visually understood.  

Python scripts are used to:  
* fetch data from DSNNow website (available in XML format)  
* store them in a MySQL database  
* generate JSON data as input for the graphs  

Graphic visualization of data is provided through [Google Charts](https://developers.google.com/chart) library, which are placed together in a [Bootstrap](https://getbootstrap.com) webpage.  

The entire project (Python scripts through cronjobs, data visualization and website front-end hosting) runs 24/7 on a Raspberry Pi Zero.  

<center><img src="https://www.raspberrypi.com/app/uploads/2018/03/RPi-Logo-Reg-SCREEN.png" height="50"></center>

### Data Visualization

#### Timelines

The upper part of the page shows the last 3 days of DSN activity as a timeline, which is organized either by station or by spacecraft. Times shown are UTC.

##### By Station

Each row is the activity of one of the DSN stations, showing also concurrent links to multiple missions served by a single antenna.  
This visualization should mimic DSN operators' console screens, where a detailed time scheduling for each of the stations' links is presented.  
Some patterns are clearly visible, for example when missions need continuous coverage the link is handed over among the three DSN sites around the world, according to when they come in visibility of the spacecraft due to Earth's rotation (DSS 14-26 are located in the USA, DSS 34-43 in Australia, DSN 54-65 in Spain). 

![](https://i.imgur.com/fwqg8Pu.png)

##### By Spacecraft

In this visualization, each row of the timeline is a mission, so it's possible to see the coverage over time each spacecraft is getting from the Deep Space Network.  
See the example below, where it's interesting to note the 24/7 coverage reserved for [James Webb Space Telescope (JWST)](https://www.jwst.nasa.gov), which at the time of writing is in its initial deployment phase, or the pattern in subsequent links with the four satellites of the [Magnetospheric Multiscale Mission (MMS)](https://mms.gsfc.nasa.gov).  

![](https://i.imgur.com/sbUB9ZL.png)

#### Link Details

Time slots in timelines visualizations are hyperlink which, when selected, show additional information of that particular link in the lower half of the page.  
Together with information about the selected slot in particular, a historical view of characteristics such as range from Earth, data rate and received power is shown for the selected spacecraft.  

![](https://i.imgur.com/8R1lrzf.png)

#### Spacecraft Range  

In this visualization, for all the tracked spacecraft the current distance from Earth is shown.  

Three plots are generated with different scales, in order to be clearly visible:  
* *Earth orbit*, meaning [HEO](https://en.wikipedia.org/wiki/Highly_elliptical_orbit) and L1-L2 [Lagrange Points](https://en.wikipedia.org/wiki/Lagrange_point) (DSN is not involved with LEO, MEO and GEO satellites)  
* *Solar System*, Neptune's orbit being at about 4.5 billion km from the Sun (the fleet of spacecraft in Mars orbit is clearly visible)
* *Beyond Solar System*, where we only find [New Horizons](https://www.nasa.gov/mission_pages/newhorizons/main/index.html) and the two [Voyager](https://voyager.jpl.nasa.gov) probes  

![](https://i.imgur.com/5XhQxvS.png)

### Dependencies

* Python modules
	* pymysql
	* xml.etree
	* pytz
	* gviz_api
	* operator
	* datetime
	* pprint, argparse, subprocess (for debugging purposes)
* a MySQL database
* [Google Charts](https://developers.google.com/chart) library
* [Bootstrap](https://getbootstrap.com) installation ([Gentelella](https://github.com/ColorlibHQ/gentelella) template has been used in this project)

### Setup

* Install a MySQL database, and upload the sample SQL dump provided in this repository
* Install the Gentelella Bootstrap template (in my case it is hosted at a separate path in the Raspberry Pi, and it's accessible online)
* Update the `config.py` file with your database login information and the URL pointing at your Bootstrap installation

### Usage

In order to periodically fetch the data from DSNNow and to generate the data to be visualized by Google Charts, the following Python scripts have to be called at regular intervals, for example with cronjobs:
* `DSNMonitor_fetchDSNNow.py` to fetch DSNNow public data (fetching interval, i.e. the cronjob period, should match the `FETCH_INTERVAL` constant in `config.py`)
* `DSNMonitor_generateDashboardData.py` to generate JSON data as chart inputs (its cronjob period only affects the front-end update)
* `DSNMonitor_generateHistoryData.py` to generate historical table in database, with a single record per day (can be ran daily)

*Example usage:*
```
*/5 * * * * python3 DSNMonitor_fetchDSNNow.py
*/15 * * * * python3 DSNMonitor_generateDashboardData.py
@daily python3 DSNMonitor_generateHistoryData.py
```
The argument `--debug` can be used for debug purposes.

## License

This project is licensed under the MIT License - see the LICENSE.md file for details.

## Acknowledgments

This project is an alternative visualization of public real-time data released by NASA at the website [DSNNow](https://eyes.nasa.gov/dsn/dsn.html), which has to be acknowledged for its policy regarding public data releasing and general audience outreaching.