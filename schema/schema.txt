import everything from 'http://ns.nuke24.net/Schema/'
import everything from 'http://ns.nuke24.net/Schema/DataTypeTranslation/'
import everything from 'http://ns.nuke24.net/Schema/Application/'
import 'http://ns.nuke24.net/Schema/RDB/Sequence'
import 'http://ns.nuke24.net/Schema/RDB/initialValue'
import 'http://ns.nuke24.net/Schema/RDB/defaultValueSequence'
import 'http://ns.nuke24.net/Schema/RDB/isAutoIncremented'
import 'http://ns.nuke24.net/Schema/RDB/isSelfKeyed'
import 'http://www.w3.org/2000/01/rdf-schema#isSubclassOf' as 'extends'

class 'integer' :
        SQL type @ "INT" :
        PHP type @ "int" : JSON type @ "number"
class 'unsigned integer' : extends(integer) :
	SQL type @ "INT UNSIGNED" : regex @ "\\d+"
class 'boolean' :
        SQL type @ "BOOLEAN" :
        PHP type @ "bool" : JSON type @ "boolean"
class 'string' :
        SQL type @ "VARCHAR(127)" :
        PHP type @ "string" : JSON type @ "string"
class 'normal ID' : extends(unsigned integer)
class 'entity ID' : extends(unsigned integer) : SQL type @ "BIGINT"
class 'code' : extends(string) : SQL type @ "CHAR(4)" : regex @ "[A-Za-z0-9 _-]{1,4}"
class 'text' : extends(string) : SQL type @ "TEXT"
class 'hash' : extends(string) : regex @ "[A-Fa-f0-9]{40}" : comment @ "Hex-encoded SHA-1 of something (40 bytes)"
class 'e-mail address' : extends(string)
class 'URI' : extends(string)
class 'time' : extends(string) : SQL type @ "TIMESTAMP"
class 'date' : extends(string) : SQL type @ "DATE"

sequence 'new entity ID' : initial value @ 1001

field modifier 'AIPK' = normal ID : is auto-incremented : key(primary)
field modifier 'EIPK' = entity ID : default value sequence @ new entity ID : key(primary)
# SRC = 'standard resource class'
field modifier 'SRC' = has a database table : has a REST service

class 'user' : SRC : members are public {
	ID : EIPK
	username : string
	passhash : hash : nullable
	e-mail address : e-mail address : nullable
}

class 'organization' : SRC : members are public {
	ID : EIPK
	name : string
}

class 'user organization attachment' : SRC : members are public : self-keyed {
	user : reference(user) {
		ID = user ID
	}
	organization : reference(organization) {
		ID = organization ID
	}
}
