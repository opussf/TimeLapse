INEED_MSG_ADDONNAME = "TimeLapse";
INEED_MSG_VERSION   = GetAddOnMetadata(INEED_MSG_ADDONNAME,"Version");
INEED_MSG_AUTHOR    = "opussf";

-- Colours
COLOR_RED = "|cffff0000";
COLOR_GREEN = "|cff00ff00";
COLOR_BLUE = "|cff0000ff";
COLOR_PURPLE = "|cff700090";
COLOR_YELLOW = "|cffffff00";
COLOR_ORANGE = "|cffff6d00";
COLOR_GREY = "|cff808080";
COLOR_GOLD = "|cffcfb52b";
COLOR_NEON_BLUE = "|cff4d4dff";
COLOR_END = "|r";

TL_Options = {}
TL = {}

function TL.Print( msg, showName)
	-- print to the chat frame
	-- set showName to false to suppress the addon name printing
	if (showName == nil) or (showName) then
		msg = COLOR_PURPLE..INEED_MSG_ADDONNAME.."> "..COLOR_END..msg
	end
	DEFAULT_CHAT_FRAME:AddMessage( msg )
end
function TL.OnLoad()
	SLASH_TIMELAPSE1 = "/TIMELAPSE"
	SLASH_TIMELAPSE2 = "/TL"
	SlashCmdList["TIMELAPSE"] = function(msg) TL.command(msg); end
end
--------------
function INEED.ADDON_LOADED()
	-- Unregister the event for this method.
	INEED_Frame:UnregisterEvent("ADDON_LOADED")

	-- Setup needed variables
	INEED.name = UnitName("player")
	INEED.realm = GetRealmName()
	INEED.faction = UnitFactionGroup("player")

	-- Setup game settings
	GameTooltip:HookScript("OnTooltipSetItem", INEED.hookSetItem)
	ItemRefTooltip:HookScript("OnTooltipSetItem", INEED.hookSetItem)
	--INEED.Orig_GameTooltip_SetCurrencyToken = GameTooltip.SetCurrencyToken  -- lifted from Altaholic (thanks guys)
	--GameTooltip.SetCurrencyToken = INEED.hookSetCurrencyToken

	-- Load Options panel
	INEED.OptionsPanel_Reset()

	INEED.Print("Loaded")
end
function TL.OnUpdate()
end
-- Non Event functions
function TL.parseCmd(msg)
	if msg then
		local i,c = strmatch(msg, "^(|c.*|r)%s*(%d*)$")
		if i then  -- i is an item, c is a count or nil
			return i, c
		else  -- Not a valid item link
			msg = string.lower(msg)
			local a,b,c = strfind(msg, "(%S+)")  --contiguous string of non-space characters
			if a then
				-- c is the matched string, strsub is everything after that, skipping the space
				return c, strsub(msg, b+2)
			else
				return ""
			end
		end
	end
end
function INEED.command(msg)
	local cmd, param = INEED.parseCmd(msg);
	--INEED.Print("cl:"..cmd.." p:"..(param or "nil") )
	local cmdFunc = INEED.CommandList[cmd];
	if cmdFunc then
		cmdFunc.func(param);
	elseif ( cmd and cmd ~= "") then  -- exists and not empty
		--INEED.Print("cl:"..cmd.." p:"..(param or "nil"))
		--param, targetString = INEED.parseTarget( param )
		INEED.addItem( cmd, tonumber(param) )
		INEED.makeOthersNeed()
		--[[
		if targetString then
			INEED.addTarget( cmd, tonumber(param), targetString )
		end
		]]
		--InterfaceOptionsFrame_OpenToCategory(FB_MSG_ADDONNAME);
	else
		INEED.PrintHelp()
	end
end
function INEED.PrintHelp()
	INEED.Print(INEED_MSG_ADDONNAME.." by "..INEED_MSG_AUTHOR);
	for cmd, info in pairs(INEED.CommandList) do
		INEED.Print(string.format("%s %s %s -> %s",
			SLASH_INEED1, cmd, info.help[1], info.help[2]));
	end
end
-- this needs to be at the end because it is referencing functions
INEED.CommandList = {
	["help"] = {
		["func"] = INEED.PrintHelp,
		["help"] = {"","Print this help."},
	},
	["list"] = {
		["func"] = INEED.showList,
		["help"] = {"", "Show a list of needed items"},
	},
	["account"] = {
		["func"] = INEED.accountInfo,
		["help"] = {"[amount]", "Show account info, and set a new amount"},
	},
	["<link>"] = {
		["func"] = INEED.PrintHelp,
		["help"] = {"[quantity]", "Set quantity needed of <link>"},
	},
	["options"] = {
		["func"] = function() InterfaceOptionsFrame_OpenToCategory( INEED_MSG_ADDONNAME ) end,
		["help"] = {"", "Open the options panel"},
	},
	["remove"] = {
		["func"] = INEED.remove,
		["help"] = {"<name>-<realm>", "Removes <name>-<realm>"},
	},
	["test"] = {
		["func"] = INEED.test,
		["help"] = {"","Do something helpful"},
	},
}

