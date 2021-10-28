from libretranslatepy import LibreTranslateAPI 
import sys

lt = LibreTranslateAPI("https://translate.astian.org/")
# print(lt.detect("Hello World"))
# print(lt.languages())
print(lt.translate(' '.join(sys.argv[1:]), "en", "fr"))

