"""Blender: turn the supplied website GLB into three neutral editable wrap panels.
No edits to the owner's original .blend, artwork or reference website.
"""
import bpy, sys, argparse
from mathutils import Vector
args = argparse.ArgumentParser()
args.add_argument('--source', required=True)
args.add_argument('--output', required=True)
opt = args.parse_args(sys.argv[sys.argv.index('--')+1:])
bpy.ops.object.select_all(action='SELECT')
bpy.ops.object.delete(use_global=False)
bpy.ops.import_scene.gltf(filepath=opt.source)
front = [o for o in bpy.data.objects if o.type == 'MESH' and o.name in ['Brand Front | curved artwork panel', 'Brand Top | artwork', 'Brand Bottom | artwork']]
# Coordinates after import are Blender Z-up, with the front toward negative Y.
positions = [o.matrix_world @ v.co for o in front for v in o.data.vertices]
x0,x1 = min(v.x for v in positions),max(v.x for v in positions)
z0,z1 = min(v.z for v in positions),max(v.z for v in positions)
for panel in ['front','left','right']:
    objects = front if panel == 'front' else [o for o in bpy.data.objects if o.type == 'MESH' and o.name.startswith('Brand '+panel.title()+' |')]
    if not objects: raise RuntimeError('Missing wrap panel '+panel)
    material = bpy.data.materials.new('BVS wrap '+panel)
    material.use_nodes = True
    shader = material.node_tree.nodes.get('Principled BSDF')
    shader.inputs['Base Color'].default_value = (.0,.135,.114,1)
    shader.inputs['Roughness'].default_value = .75
    shader.inputs['Metallic'].default_value = 0
    shader.inputs['Specular IOR Level'].default_value = .1
    # glTF needs an initial UV-bearing image for runtime texture replacement.
    image = bpy.data.images.new('Neutral '+panel, width=4, height=4)
    image.generated_color = (.0,.135,.114,1)
    image.pack()
    texture = material.node_tree.nodes.new('ShaderNodeTexImage'); texture.image = image
    material.node_tree.links.new(texture.outputs['Color'],shader.inputs['Base Color'])
    points = [o.matrix_world @ v.co for o in objects for v in o.data.vertices]
    y0,y1 = min(v.y for v in points),max(v.y for v in points)
    for obj in objects:
        obj.data.materials.clear(); obj.data.materials.append(material)
        uv = obj.data.uv_layers.active or obj.data.uv_layers.new(name='BVS visual wrap')
        for loop in obj.data.loops:
            pos = obj.matrix_world @ obj.data.vertices[loop.vertex_index].co
            u = (pos.x-x0)/(x1-x0) if panel=='front' else (pos.y-y0)/(y1-y0)
            if panel == 'left': u = 1-u
            uv.data[loop.index].uv = (u,(pos.z-z0)/(z1-z0))
        obj['bvs_wrap_panel'] = panel
bpy.ops.object.select_all(action='SELECT')
bpy.ops.export_scene.gltf(filepath=opt.output, export_format='GLB', use_selection=True, export_apply=True, export_animations=False, export_extras=True, export_yup=True)
print('BVS design model exported:',opt.output)
