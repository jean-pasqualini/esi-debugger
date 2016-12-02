# esi-debugger

#### Description

The esi debugger is esi debugger.

Is a php proxy, parse esi.

It transform esi to iframe and add color for identify caching strategy.

It also allows you to identify bad design

#### Install steps

1. Install composer

2. Run composer \create-project jean-pasqualini/esi-debugger ./esi-debugger

3. Go to ./esi-debugger

1. Create config.yml at ./

2. Copy and paste
```
esidebugger:
    baseUrl: http://url.fr
    patternFragment: fragment
    mode: iframe
```

3. Modify the config for your usage

#### Run

4. Run ./run-debug