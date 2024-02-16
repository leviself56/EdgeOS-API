# EdgeOS REST API
## Php code for querying statistics from Ubiquiti Edgeswitch

Server will need access to the switch in the same subnet/vlan.
The file `api.php` allows a remote server (like Zabbix) to query the switch and retrieve the datasets via a HTTP POST.
This code can be further expanded to include POST, PATCH or PUT to manipulate the remote switch.

Current functions:
+ `get.sfps`
+ `get.interfaces`
+ `get.system.info`




Example usage:

```POST http://localhost/EdgeOS/api.php```

```Content-Type: application/json```
```
{
  "ip": "10.15.100.170",
  "username": "ubnt",
  "password": "ubnt",
  "function": "get.sfps"
}
```


Sample Response:
```
{
  "0/1": {
    "temperature": 24,
    "voltage": 3.281,
    "current": 0,
    "rxPower": -40,
    "txPower": -40
  },
  "0/2": {
    "temperature": 23.5,
    "voltage": 3.341,
    "current": 37.17,
    "rxPower": -7.552,
    "txPower": -2.184
  },
  "0/3": {
    "temperature": 18.6,
    "voltage": 3.291,
    "current": 17.982,
    "rxPower": -11.249,
    "txPower": -5.612
  }
}
```
