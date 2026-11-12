# Flutter API client

This folder contains a reusable Dart client for the Laravel API. The configured
The current base URL is `http://192.168.56.1:8000/api`. Change the `baseUrl`
constant in `lib/services/api_client.dart` when the host moves to another
network. The previous address was `http://192.168.73.142:8000/api`.

## Add to an existing Flutter app

Copy `lib/services/api_client.dart` into the Flutter app and add the `http`
package to that app's `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.4.0
```

For Android, allow Internet access and cleartext HTTP in
`android/app/src/main/AndroidManifest.xml`:

```xml
<uses-permission android:name="android.permission.INTERNET" />

<application
    android:usesCleartextTraffic="true"
    ...>
```

This setting is suitable only for this local development setup. Use HTTPS and
disable cleartext traffic when deploying outside the trusted LAN. For iOS,
configure local-network access and transport security according to the app's
deployment policy; do not enable broad ATS exceptions for a production app.

The phone must be connected to the same Wi-Fi as the Laravel host. If the
request times out, check Windows Firewall inbound access for TCP port `8000`
and verify the host still has address `192.168.56.1`. This address belongs to
the host's virtual `Ethernet 2` adapter; the phone must have a route to that
adapter for it to be reachable.

## Request examples

```dart
final api = ApiClient();

try {
  final login = await api.login(
    identifier: phoneNumber,
    password: password,
  );
  api.setBearerToken(login.data['token'] as String);

  final status = await api.getRegionStatus();
  print(status.data);

  final history = await api.getEarthquakeHistory(perPage: 20);
  print(history.data['items']);
} on ApiException catch (error) {
  print('API ${error.statusCode}: ${error.message}');
} finally {
  api.close();
}
```

`submitCitizenResponse` requires the logged-in user's ID and a status of
`AMAN` or `BUTUH_BANTUAN`. `sendTelemetry` is intended for a trusted device
client; the ESP32 must also send its provisioned Node 1 token in the
`X-Device-Token` header. Do not embed that device secret in a distributed
Flutter app.

The sample in `example/status.dart` can be run with `dart run
example/status.dart` from this folder.
