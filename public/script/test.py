from libretranslatepy import LibreTranslateAPI

lt = LibreTranslateAPI("https://translate.astian.org/")
print(lt.detect("Hello World"))
print(lt.languages())
print(lt.translate("LibreTranslate is awesome!", "en", "es"))
