# Sprout Cache
A smart graph caching system that can be used to clear transients on certain actions or based on logic, not just on time. Often used together with the SproutServices to output data from private parts of a system that need to be re-generated every time the authorized user re-accesses the resource that generated the data to begin with.

## Use Cases
#### Delete a transient when a specific post updates.
Often times, instead of generating a lot of things for a post page, for example a recipe or a gallery that requires heavy computation in terms of retrieving remote images and such, we can transient it all. But do we want it to *ever* expire? Yes and no. We'd like for it to not expire based on time, but rather action, as such, you will be saving your transient as `gallery_transient` and declare that you'd want it cleared when the post with ID `48` is deletes, as such, the hook you're looking for to use as a "clear point" is `save_post` and check if the ID corresponds to what you need.

#### Get output data from restricted areas.
Assume non-authorized others want to see the output of something, but you wouldn't like to give them access to the module itself since it's dangerous for them to be able to interact with it - save the output and update it every time an authorized user re-accesses the said resource that generated the output.
