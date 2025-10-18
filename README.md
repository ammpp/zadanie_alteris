# zadanie alteris

**GET /api/material - Get all materials**

**GET /api/material/{id} - Get specified material**

**POST /api/material - Create material**

    kod (string) - code
    nazwa (string) - name
    grupa (int) - group id
    jednostka (int) - unit id
    wartosc (float) - value

**PUT /api/material/{id} - Edit material**

    kod (string) - code
    nazwa (string) - name
    grupa (int) - group id
    jednostka (int) - unit id
    wartosc (float) - value

**DELETE /api/material/{id} - Delete material**

**GET /api/grupa - List of groups**

**GET /api/grupa/{id} - List of groups starting from ID**

**POST /api/grupa - Create group**

    nazwa (string) - name
    parent (int) - parent group id (0 for main group)

**PUT /api/grupa/{id} - Edit group**

    nazwa (string) - name
    parent (int) - parent group id (0 for main group)

**DELETE /api/grupa/{id} - Delete group**

**GET /api/jednostka - List of units**

**GET /api/jednostka/{id} - Get specified unit**

**POST /api/jednostka - Create unit**

    skrot (string) - short name
    nazwa (string) - name

**PUT /api/jednostka/{id} - Edit unit**

    skrot (string) - short name
    nazwa (string) - name

**DELETE /api/jednostka/{id} - Delete unit**
