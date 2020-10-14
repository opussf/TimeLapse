#!/usr/bin/env lua

require "wowTest"

test.outFileName = "testOut.xml"

-- Figure out how to parse the XML here, until then....
TIMELAPSE_Frame = Frame

-- require the file to test
ParseTOC( "../src/TimeLapse.toc" )

function test.before()
	TL.OnLoad()
end
function test.after()
end

function test.test_SetDisable()
	TL.command("disable")
	assertIsNil( TL_Options.Enabled )
end
function test.test_SetEnable()
	TL_Options.Enabled = nil
	TL.command("enable")
	assertTrue( TL_Options.Enabled )
end
function test.test_SetOff()
	TL.command("off")
	assertIsNil( TL_Options.Enabled )
end
function test.test_SetOn()
	TL_Options.Enabled = nil
	TL.command("on")
	assertTrue( TL_Options.Enabled )
end
function test.test_Status()
	TL.command("status")
end
function test.test_SetDelay_0()
	TL.command("delay 0")
	assertEquals(1, TL_Options.Delay )
end
function test.test_SetDelay_Nil()
	TL_Options.Delay = 60;
	TL.command("delay")
	assertEquals( 60, TL_Options.Delay )
end
function test.test_SetDelay_180()
	TL.command("delay 180")
	assertEquals( 180, TL_Options.Delay )
end
function test.test_Help()
	TL.command("help")
end
function test.test_NoCommand()
	TL.command(" ")
end


test.run()