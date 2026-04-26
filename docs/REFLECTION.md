## Hogyan értelmezted a 4. pont szűrés/rendezés követelményt, és miért pont úgy döntöttél?
Mivel nem volt specifikálva a szűrés, és az API doksi alapján erre nem láttam lehetőséget, ezért megoldásként frontenden js-el oldottam meg.
A szűrés érvényes minden megjelenített adatra, a sorba rendezés pedig a táblázat header-ekre kattintva működik. 
Ezzel véleményem szerint teljesen lefedésre kerül az elvárt eredmény.

## Melyik AI modell-t használta és miért?
A feladat során Claude AI-t használtam, mivel eddigi tapasztalataim alapján sokkal biztosabban dolgozik, és nagyobb feladatok is rábízhatóak, valamint a planning mode-al iszonyastosan meggyorsította a feladatok elvégzését. Emelett ezzel van jelenleg a legrelevánsabb tapasztalatom

## Hol segített az AI, hol kellett korrigálni vagy más modellt bevonni?
A feldat során végig használtam, a project setuptól kezdve a core logika bekötésén át egészen a frontend össze rakásáig. 
Kisebb javításokra volt szükség az API válaszok feldolgozása és megjelenítése során mivel a kapott válasz struktúráját nem megfelelően kezelte, valamint minimálisan a generált kódban váltózó/függvény/class nevek változtatására volt szükség.

## Mi az, amit ha újra csinálnád, másképp csinálnál?
Több időt eltöltenék azzal, hogy megpróbáljam az API első verzióját bekötni, mivel ott ahogy láttam, van lehetőség a listák feliratkozóinak számának lekérdezésére.

Emelett mivel nem találtam olyan endpointot, amely vissza adná a listák created date-jét, esetleg kiirattam volna az első feliratkozás dátumát, azonban ezt nem találtam megbízható megoldásnak, mivel előfordulhatnak üres listák is, így ez az adat változatlanul üres maradna.
