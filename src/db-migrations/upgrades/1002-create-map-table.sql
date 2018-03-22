CREATE TABLE map_generation (
	generation_id text, -- probably a uuid
	generation_start_time timestamp,
	generator_node_name text, -- e.g. togos-fbs
	factorio_commit_id text,
	data_commit_id text,
	map_seed integer,
	map_scale integer,
	map_width integer, -- Also the height!
	map_offset_x integer,
	map_offset_y integer,
	map_image_urn text,
	generation_end_time timestamp,
	compilation_reported_elapsed_time real, -- number of seconds generator compilation took, according to factorio
	generation_reported_elapsed_time real -- number of seconds generation took
);

CREATE TABLE resource_stats (
       generation_id text,
       resource_name text,
       total_quantity real,
       average_quantity real,
       max_unclamped_probability real,
       max_richness real,
       average_richness real
);
